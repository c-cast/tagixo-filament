<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;

final class ToggleField implements FilamentFieldModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentField(
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $name = self::resolveStatePath($field);
        if ($name === null) {
            return null;
        }

        $component = Toggle::make($name);

        if (array_key_exists('default_checked', $field)) {
            $component->default((bool) $field['default_checked']);
        }

        if (array_key_exists('inline', $field)) {
            $component->inline((bool) $field['inline']);
        }

        return self::applyCommonFieldConfig($component, $field);
    }
}
