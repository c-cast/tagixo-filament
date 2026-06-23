<?php

namespace Ccast\TagixoFilament\Facades;

use Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry;
use Illuminate\Support\Facades\Facade;

/**
 * TagixoReactivity
 *
 * Laravel facade over the singleton
 * {@see ReactivityFunctionRegistry}.
 *
 * This is the canonical entry point for consumer apps and plugins that
 * want to extend the Tagixo reactivity expression sandbox with custom
 * whitelisted functions.
 *
 * ### Typical usage
 *
 * In `AppServiceProvider::boot()`:
 *
 * ```php
 * use Ccast\TagixoFilament\Facades\TagixoReactivity;
 *
 * TagixoReactivity::register('shout', fn (string $value) => strtoupper($value));
 *
 * TagixoReactivity::registerMany([
 *     'tenant_name' => fn () => tenant()->name,
 *     'currency'    => fn (float $amount, string $code = 'EUR') => number_format($amount, 2) . ' ' . $code,
 * ]);
 *
 * TagixoReactivity::addProvider(new \App\Reactivity\TenantFunctionProvider());
 * TagixoReactivity::addProvider(fn () => new \App\Reactivity\LocaleFunctionProvider());
 * ```
 *
 * @method static \Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry register(string $name, \Closure $evaluator)
 * @method static \Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry registerMany(array $functions)
 * @method static \Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry addProvider(\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface|string|\Closure $provider)
 * @method static array resolveAll()
 * @method static array describe()
 * @method static \Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry flush()
 *
 * @see ReactivityFunctionRegistry
 */
class TagixoReactivity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ReactivityFunctionRegistry::class;
    }
}
