<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\MapsFilamentFieldModule;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Component;

final class TextAreaField implements FilamentFieldModule
{
    use MapsFilamentFieldModule;

    protected static function makeFilamentComponent(
        string $name,
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $component = Textarea::make($name);

        $rows = $field['rows'] ?? null;
        if (is_numeric($rows)) {
            $component->rows((int) $rows);
        }

        return $component;
    }
}
