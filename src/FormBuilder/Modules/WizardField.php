<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Wizard;

final class WizardField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $wizard = Wizard::make($children);

        if (! empty($field['skippable']) && method_exists($wizard, 'skippable')) {
            $wizard->skippable();
        }

        $startOn = $field['start_on_step'] ?? null;
        if (is_numeric($startOn) && (int) $startOn > 0 && method_exists($wizard, 'startOnStep')) {
            $wizard->startOnStep((int) $startOn);
        }

        return self::applyLayoutConfig($wizard, $field);
    }
}
