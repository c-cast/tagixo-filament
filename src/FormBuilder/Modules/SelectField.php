<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Arr;

final class SelectField implements FilamentFieldModule
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

        $component = Select::make($name);
        $options = self::resolveOptions($field, $modelRegistry, $modelOptionsResolver);

        if (! empty($options)) {
            $component->options($options);
        }

        $isMultiple = (bool) ($field['multiple'] ?? false);
        if ($isMultiple) {
            $component->multiple();
        }

        $component = self::applyCommonFieldConfig($component, $field);

        if ($isMultiple && isset($field['default_value'])) {
            $component->default(self::normalizeMultipleDefault($field['default_value']));
        }

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
