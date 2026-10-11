<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Tagixo\Filament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Arr;

final class RadioField implements FilamentFieldModule
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

        $component = Radio::make($name);
        $options = self::resolveOptions($field, $modelRegistry, $modelOptionsResolver);

        if (! empty($options)) {
            $component->options($options);
        }

        if (! empty($field['inline'])) {
            $component->inline();
        }

        $component = self::applyCommonFieldConfig($component, $field);

        if (
            ! empty($options) &&
            ! empty(Arr::get($field, 'validation.in_options'))
        ) {
            $allowedValues = implode(',', array_keys($options));
            $component->rule("in:{$allowedValues}");
        }

        return $component;
    }
}
