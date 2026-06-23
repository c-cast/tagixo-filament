<?php

namespace Ccast\TagixoFilament\FormBuilder\Contracts;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Closure;
use Filament\Schemas\Components\Component;

interface FilamentWrapperModule
{
    /**
     * @param  array<string, mixed>  $field
     * @param  array<Component>  $children
     * @param  (Closure(string, ?string, ?string, array): array)|null  $modelOptionsResolver
     */
    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component;
}
