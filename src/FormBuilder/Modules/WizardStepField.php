<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Wizard\Step;

final class WizardStepField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $label = trim((string) ($field['label'] ?? '')) ?: 'Step';
        $step = Step::make($label)->schema($children);

        $description = trim((string) ($field['description'] ?? ''));
        if ($description !== '' && method_exists($step, 'description')) {
            $step->description($description);
        }

        $icon = trim((string) ($field['icon'] ?? ''));
        if ($icon !== '' && method_exists($step, 'icon')) {
            $step->icon($icon);
        }

        $columns = self::buildResponsiveColumns($field, 1);
        if (method_exists($step, 'columns')) {
            $step->columns($columns);
        }

        return $step;
    }
}
