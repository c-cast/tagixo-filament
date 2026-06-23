<?php

namespace Ccast\TagixoFilament\FormBuilder\Reactivity;

use Ccast\TagixoFilament\Facades\TagixoReactivity;
use Ccast\TagixoFilament\FormBuilder\Reactivity\Contracts\ReactivityFunctionProvider;
use Closure;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * ReactivityFunctionRegistry
 *
 * Singleton registry that collects additional sandboxed functions for the
 * Tagixo reactivity expression language. Consumer applications (and their
 * plugins) use this registry — typically via the
 * {@see TagixoReactivity} facade — to extend
 * the built-in whitelist defined by {@see ReactivityExpressionEvaluator}
 * without modifying vendor code.
 *
 * The registry supports three layers:
 *
 *  1. **Direct closures** — `register('name', fn (...) => ...)` attaches a
 *     single PHP callable as an expression function. This is the simplest
 *     path for quick, app-specific helpers.
 *
 *  2. **Provider classes** — `addProvider(new MyProvider())` accepts any
 *     {@see ReactivityFunctionProvider} (or the raw Symfony interface) and
 *     contributes all of its `getFunctions()` at evaluation time.
 *
 *  3. **Deferred (lazy) providers** — `addProvider(fn () => new MyProvider())`
 *     accepts a `Closure` that returns a provider instance. The closure is
 *     re-invoked on every call to `resolveAll()`, which is how multi-tenant
 *     apps expose a *different* function set per request (e.g. tenant-scoped
 *     currency, locale, or feature flags).
 *
 * The registry is tenant-agnostic. Tagixo itself never assumes a multi-tenant
 * context. Apps that need per-tenant isolation should bind tenant-aware
 * closures as deferred providers in their service container.
 *
 * ### Resolution order
 *
 * When {@see ReactivityExpressionEvaluator} calls {@see resolveAll()}, functions
 * are returned in the following order (last entry wins for duplicate names):
 *
 *   1. Direct closures (in registration order).
 *   2. Provider instances (in registration order).
 *   3. Deferred providers (in registration order, each invoked fresh).
 *
 * The evaluator then registers them on top of its built-in whitelist. A
 * plugin function with the same name as a built-in will override the
 * built-in — consumer apps are trusted to avoid accidental shadowing.
 *
 * ### Security
 *
 * Every function contributed through the registry runs inside the sandbox
 * with the same guarantees as built-ins: no access to `eval`, `new`, class
 * constants, properties, method calls, or arbitrary globals. Providers can
 * only register explicit function callables. If a provider closure itself
 * throws, the evaluator catches the exception, reports it via
 * `report($e)`, and returns `null` from the expression — preventing a
 * broken plugin from killing the whole form render.
 */
final class ReactivityFunctionRegistry
{
    /**
     * Direct single-function registrations: name => [compiler, evaluator].
     *
     * @var array<string, array{Closure, Closure}>
     */
    private array $directFunctions = [];

    /**
     * Eager provider instances registered via addProvider().
     *
     * @var array<int, ExpressionFunctionProviderInterface>
     */
    private array $providers = [];

    /**
     * Deferred providers: closures that return a provider instance on each
     * resolveAll() call. Used for per-request / per-tenant function sets.
     *
     * @var array<int, Closure>
     */
    private array $deferredProviders = [];

    /**
     * Register a single whitelisted function from a raw PHP callable.
     *
     * The callable receives the expression arguments directly (not the
     * Symfony `array $values` first argument). The registry wraps it in
     * the Symfony-compatible signature internally.
     *
     * Example:
     *
     *     $registry->register('shout', fn (string $value) => strtoupper($value));
     *     // Expression: shout('hello') → 'HELLO'
     *
     * @param  string  $name  Function name exposed inside expressions.
     * @param  Closure  $evaluator  Runtime implementation. Called with the raw
     *                              expression arguments.
     * @return $this
     */
    public function register(string $name, Closure $evaluator): self
    {
        $name = trim($name);

        if ($name === '') {
            return $this;
        }

        $noopCompiler = static fn (...$args): string => 'null';

        // Wrap the user callable so it matches Symfony's signature:
        // fn (array $values, mixed ...$args) => mixed
        $wrapped = static fn (array $values, mixed ...$args) => $evaluator(...$args);

        $this->directFunctions[$name] = [$noopCompiler, $wrapped];

        return $this;
    }

    /**
     * Register many direct functions at once.
     *
     * @param  array<string, Closure>  $functions  name => evaluator
     * @return $this
     */
    public function registerMany(array $functions): self
    {
        foreach ($functions as $name => $evaluator) {
            if (is_string($name) && $evaluator instanceof Closure) {
                $this->register($name, $evaluator);
            }
        }

        return $this;
    }

    /**
     * Attach a provider contributing one or more expression functions.
     *
     * Accepts three shapes:
     *
     *  - An instance implementing {@see ReactivityFunctionProvider} (or the
     *    raw Symfony {@see ExpressionFunctionProviderInterface}).
     *  - A class-string of such a provider — the registry will instantiate
     *    it lazily with `new $class()` on every `resolveAll()` call.
     *  - A Closure returning a provider instance — invoked fresh on every
     *    `resolveAll()` call (deferred / per-request).
     *
     * @param  ExpressionFunctionProviderInterface|class-string|Closure  $provider
     * @return $this
     */
    public function addProvider(ExpressionFunctionProviderInterface | string | Closure $provider): self
    {
        if ($provider instanceof Closure) {
            $this->deferredProviders[] = $provider;

            return $this;
        }

        if (is_string($provider)) {
            if (! class_exists($provider)) {
                return $this;
            }

            $this->deferredProviders[] = static fn () => new $provider;

            return $this;
        }

        $this->providers[] = $provider;

        return $this;
    }

    /**
     * Resolve every registered function into a flat list of
     * {@see ExpressionFunction} objects ready to hand to Symfony's
     * ExpressionLanguage.
     *
     * This method is called once per `ReactivityExpressionEvaluator::evaluate()`
     * invocation. Deferred providers are re-executed on every call, which
     * is what enables per-request / per-tenant function sets.
     *
     * @return array<int, ExpressionFunction>
     */
    public function resolveAll(): array
    {
        $functions = [];

        foreach ($this->directFunctions as $name => [$compiler, $evaluator]) {
            $functions[] = new ExpressionFunction($name, $compiler, $evaluator);
        }

        foreach ($this->providers as $provider) {
            foreach ($provider->getFunctions() as $fn) {
                if ($fn instanceof ExpressionFunction) {
                    $functions[] = $fn;
                }
            }
        }

        foreach ($this->deferredProviders as $factory) {
            $instance = $factory();

            if (! $instance instanceof ExpressionFunctionProviderInterface) {
                continue;
            }

            foreach ($instance->getFunctions() as $fn) {
                if ($fn instanceof ExpressionFunction) {
                    $functions[] = $fn;
                }
            }
        }

        return $functions;
    }

    /**
     * Return the list of all function names currently exposed by the
     * registry (including those contributed by providers). Useful for
     * debug tooling / builder-side autocomplete.
     *
     * @return array<int, string>
     */
    public function describe(): array
    {
        $names = [];

        foreach ($this->resolveAll() as $fn) {
            $names[] = $fn->getName();
        }

        return array_values(array_unique($names));
    }

    /**
     * Forget every registered function and provider. Primarily useful in
     * tests that need a clean slate between assertions.
     *
     * @return $this
     */
    public function flush(): self
    {
        $this->directFunctions = [];
        $this->providers = [];
        $this->deferredProviders = [];

        return $this;
    }
}
