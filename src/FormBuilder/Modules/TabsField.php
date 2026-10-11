<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Tagixo\Filament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;

final class TabsField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $label = trim((string) ($field['label'] ?? ''));
        $tabs = Tabs::make($label !== '' ? $label : 'Tabs')->tabs($children);

        return self::applyLayoutConfig($tabs, $field);
    }
}
