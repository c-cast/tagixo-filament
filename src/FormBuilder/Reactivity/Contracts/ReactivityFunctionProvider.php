<?php

namespace Ccast\TagixoFilament\FormBuilder\Reactivity\Contracts;

use Ccast\TagixoFilament\Facades\TagixoReactivity;
use Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * ReactivityFunctionProvider
 *
 * Plugin contract for extending the Tagixo reactivity expression sandbox
 * with additional whitelisted functions.
 *
 * A provider is a plain PHP class that returns a list of
 * {@see ExpressionFunction} objects.
 * Each function becomes a callable identifier inside user-written reactivity
 * expressions (e.g. `tenant_name()`, `currency(state, 'EUR')`).
 *
 * Providers are registered with the singleton
 * {@see ReactivityFunctionRegistry}
 * either directly or via the {@see TagixoReactivity}
 * facade, typically from the consumer application's `AppServiceProvider::boot()`.
 *
 * Because providers are resolved on every `ReactivityExpressionEvaluator::evaluate()`
 * call, their `getFunctions()` method may return a different set per request —
 * allowing multi-tenant apps to expose tenant-scoped helpers without leaking
 * them across requests.
 *
 * This interface is a Tagixo-specific alias of Symfony's
 * {@see ExpressionFunctionProviderInterface}. It exists so consumer apps
 * can depend on the Tagixo namespace rather than Symfony's, and so the
 * registry can type-hint against a stable contract.
 *
 * ### Example
 *
 * ```php
 * use Ccast\TagixoFilament\FormBuilder\Reactivity\Contracts\ReactivityFunctionProvider;
 * use Symfony\Component\ExpressionLanguage\ExpressionFunction;
 *
 * final class TenantFunctionProvider implements ReactivityFunctionProvider
 * {
 *     public function getFunctions(): array
 *     {
 *         return [
 *             ExpressionFunction::fromPhp('strtoupper', 'shout'),
 *             new ExpressionFunction(
 *                 'tenant_name',
 *                 static fn (): string => 'null', // compiler (unused)
 *                 static fn (array $values): string => tenant()->name,
 *             ),
 *         ];
 *     }
 * }
 * ```
 */
interface ReactivityFunctionProvider extends ExpressionFunctionProviderInterface {}
