<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Tagixo\Filament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Arr;

final class CheckboxField implements FilamentFieldModule
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

        $component = Checkbox::make($name);

        if (array_key_exists('default_checked', $field)) {
            $component->default((bool) $field['default_checked']);
        }

        $component = self::applyCommonFieldConfig($component, $field);

        if (! empty(Arr::get($field, 'validation.accepted'))) {
            $component->rule('accepted');
        }

        return $component;
    }
}
