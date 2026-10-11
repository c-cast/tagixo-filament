<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Concerns\MapsFilamentFieldModule;
use Tagixo\Filament\FormBuilder\Contracts\FilamentFieldModule;
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
