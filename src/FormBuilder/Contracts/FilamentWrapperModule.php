<?php

namespace Tagixo\Filament\FormBuilder\Contracts;

use Tagixo\Core\Services\BuilderModelRegistryService;
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
