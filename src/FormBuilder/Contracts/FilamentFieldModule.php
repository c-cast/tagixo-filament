<?php

namespace Ccast\TagixoFilament\FormBuilder\Contracts;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Closure;
use Filament\Schemas\Components\Component;

interface FilamentFieldModule
{
    /**
     * @param  array<string, mixed>  $field
     * @param  (Closure(string, ?string, ?string, array): array)|null  $modelOptionsResolver
     */
    public static function toFilamentField(
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component;
}
