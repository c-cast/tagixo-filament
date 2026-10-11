<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Tagixo\Filament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs\Tab;

final class TabField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $label = trim((string) ($field['label'] ?? '')) ?: 'Tab';
        $tab = Tab::make($label)->schema($children);

        $icon = trim((string) ($field['icon'] ?? ''));
        if ($icon !== '' && method_exists($tab, 'icon')) {
            $tab->icon($icon);
        }

        $columns = self::buildResponsiveColumns($field, 1);
        if (method_exists($tab, 'columns')) {
            $tab->columns($columns);
        }

        return $tab;
    }
}
