<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Tagixo\Filament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;

final class FieldsetField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $label = trim((string) ($field['label'] ?? '')) ?: ' ';
        $fieldset = Fieldset::make($label)->schema($children);

        $columns = self::buildResponsiveColumns($field, 2);
        if (method_exists($fieldset, 'columns')) {
            $fieldset->columns($columns);
        }

        return self::applyLayoutConfig($fieldset, $field);
    }
}
