<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

final class SectionField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $label = trim((string) ($field['label'] ?? ''));
        $section = $label !== '' ? Section::make($label) : Section::make();

        $section->schema($children);

        $description = trim((string) ($field['description'] ?? ''));
        if ($description !== '' && method_exists($section, 'description')) {
            $section->description($description);
        }

        $icon = trim((string) ($field['icon'] ?? ''));
        if ($icon !== '' && method_exists($section, 'icon')) {
            $section->icon($icon);
        }

        $columns = self::buildResponsiveColumns($field, 1);
        if (method_exists($section, 'columns')) {
            $section->columns($columns);
        }

        $collapsible = $field['collapsible'] ?? null;
        if (($collapsible === 'yes' || $collapsible === true || $collapsible === 1) && method_exists($section, 'collapsible')) {
            $section->collapsible();
        }

        $collapsed = $field['collapsed'] ?? null;
        if (($collapsed === 'yes' || $collapsed === true || $collapsed === 1) && method_exists($section, 'collapsed')) {
            $section->collapsed();
        }

        return self::applyLayoutConfig($section, $field);
    }
}
