<?php

namespace Ccast\TagixoFilament\FormBuilder\Reactivity;

use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Throwable;

/**
 * ReactivityExpressionEvaluator
 *
 * Sandboxed value calculator backed by Symfony ExpressionLanguage.
 * Users can write expressions like:
 *
 *     get('first_name') ~ ' ' ~ get('last_name')
 *     length(state) > 5 ? slug(state) : null
 *     if(state > 0, 'positive', 'non-positive')
 *
 * The expression language is Turing-incomplete, has no access to the
 * filesystem, network, DB, env, classes, or arbitrary PHP functions.
 * Only the functions explicitly registered in `registerBuiltins()` —
 * plus any extensions contributed through the singleton
 * {@see ReactivityFunctionRegistry} — are callable. This class is safe
 * for multi-tenant use.
 *
 * ### Why there is no static cache
 *
 * Earlier revisions cached the `ExpressionLanguage` instance statically to
 * avoid rebuilding the function table on every call. That cache is
 * intentionally removed: consumer apps can attach *deferred* providers to
 * {@see ReactivityFunctionRegistry} that must be re-invoked on every
 * request (e.g. tenant-scoped helpers). Constructing a fresh
 * `ExpressionLanguage` costs only a few microseconds, which is negligible
 * next to the surrounding Filament render path.
 */
final class ReactivityExpressionEvaluator
{
    /**
     * Evaluate a user-supplied expression against the current form state.
     *
     * @param  string  $expression  Raw expression from the builder.
     * @param  mixed  $state  Current value of the field that triggered the hook.
     * @param  Get  $get  Filament Get helper for reading sibling fields.
     * @param  array<string, mixed>  $extra  Extra variables to expose (e.g. 'user' => ['id' => 1, 'name' => '...']).
     * @return mixed The computed value, or null on error / blank expression.
     */
    public static function evaluate(string $expression, mixed $state, Get $get, array $extra = []): mixed
    {
        $expression = trim($expression);

        if ($expression === '') {
            return null;
        }

        $values = array_merge([
            'state' => $state,
            '_get' => $get,
        ], $extra);

        try {
            return self::buildLanguage()->evaluate($expression, $values);
        } catch (SyntaxError | Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Build a fresh sandboxed ExpressionLanguage instance for a single
     * evaluation. Registers the built-in whitelist first, then merges in
     * every function contributed by the {@see ReactivityFunctionRegistry}
     * (both eager and deferred providers).
     *
     * Plugin functions with a name clash override built-ins (last registration wins).
     */
    private static function buildLanguage(): ExpressionLanguage
    {
        $lang = new ExpressionLanguage;
        self::registerBuiltins($lang);

        try {
            /** @var ReactivityFunctionRegistry $registry */
            $registry = app(ReactivityFunctionRegistry::class);

            foreach ($registry->resolveAll() as $fn) {
                if ($fn instanceof ExpressionFunction) {
                    $lang->addFunction($fn);
                }
            }
        } catch (Throwable $e) {
            // A broken plugin must not kill the whole evaluation. Report
            // and continue with the built-in whitelist only.
            report($e);
        }

        return $lang;
    }

    /**
     * Register all built-in whitelisted functions on the given ExpressionLanguage instance.
     *
     * All functions below are pure value transforms — no I/O, DB, network, or
     * side effects. Adding new functions requires a code change (not runtime
     * configuration), which is the whole point of the sandbox.
     */
    private static function registerBuiltins(ExpressionLanguage $lang): void
    {
        // Compiler is unused (we only evaluate, never compile to PHP code),
        // so every compiler callback returns the placeholder 'null'.
        $noopCompiler = static fn (...$args): string => 'null';

        // get(path) — read another field via the Filament Get helper
        $lang->register('get', $noopCompiler, static fn (array $values, mixed $path) => ($values['_get'])((string) $path));

        // String helpers
        $lang->register('slug', $noopCompiler, static fn (array $values, mixed $value) => Str::slug((string) ($value ?? '')));
        $lang->register('upper', $noopCompiler, static fn (array $values, mixed $value) => Str::upper((string) ($value ?? '')));
        $lang->register('lower', $noopCompiler, static fn (array $values, mixed $value) => Str::lower((string) ($value ?? '')));
        $lang->register('title', $noopCompiler, static fn (array $values, mixed $value) => Str::title((string) ($value ?? '')));
        $lang->register('trim', $noopCompiler, static fn (array $values, mixed $value) => trim((string) ($value ?? '')));
        $lang->register('length', $noopCompiler, static fn (array $values, mixed $value) => mb_strlen((string) ($value ?? '')));
        $lang->register('contains', $noopCompiler, static fn (array $values, mixed $haystack, mixed $needle) => Str::contains((string) ($haystack ?? ''), (string) ($needle ?? '')));
        $lang->register('starts_with', $noopCompiler, static fn (array $values, mixed $haystack, mixed $needle) => Str::startsWith((string) ($haystack ?? ''), (string) ($needle ?? '')));
        $lang->register('ends_with', $noopCompiler, static fn (array $values, mixed $haystack, mixed $needle) => Str::endsWith((string) ($haystack ?? ''), (string) ($needle ?? '')));
        $lang->register('replace', $noopCompiler, static fn (array $values, mixed $value, mixed $search, mixed $replacement) => str_replace((string) ($search ?? ''), (string) ($replacement ?? ''), (string) ($value ?? '')));

        // Date/time helpers
        $lang->register('now', $noopCompiler, static fn (array $values) => now()->toIso8601String());
        $lang->register('date', $noopCompiler, static fn (array $values, mixed $format) => now()->format((string) ($format ?? 'Y-m-d')));

        // Casts
        $lang->register('int', $noopCompiler, static fn (array $values, mixed $value) => (int) $value);
        $lang->register('float', $noopCompiler, static fn (array $values, mixed $value) => (float) $value);
        $lang->register('bool', $noopCompiler, static fn (array $values, mixed $value) => self::toBool($value));
        $lang->register('json', $noopCompiler, static fn (array $values, mixed $value) => json_encode($value));

        // Logical helpers
        $lang->register('coalesce', $noopCompiler, static function (array $values, mixed ...$args): mixed {
            foreach ($args as $arg) {
                if ($arg !== null) {
                    return $arg;
                }
            }

            return null;
        });
        $lang->register('if', $noopCompiler, static fn (array $values, mixed $cond, mixed $a, mixed $b) => $cond ? $a : $b);
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value !== 0.0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return ! in_array($normalized, ['', '0', 'false', 'no', 'off', 'null'], true);
        }

        return (bool) $value;
    }
}
