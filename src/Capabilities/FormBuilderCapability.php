<?php

namespace Tagixo\Filament\Capabilities;

use Filament\Panel;
use Tagixo\Filament\Forms\PropTypes\BooleanFilamentTablePropType;
use Tagixo\Filament\Forms\PropTypes\DateFilamentTablePropType;
use Tagixo\Filament\Forms\PropTypes\FileFilamentTablePropType;
use Tagixo\Filament\Forms\PropTypes\FilamentTablePropType;
use Tagixo\Filament\FormBuilder\Modules\ToggleModule;
use Tagixo\Filament\Resources\FormResource;
use Tagixo\Filament\TagixoFilamentPlugin;
use Tagixo\Core\Facades\Tagixo;
use Tagixo\FormBuilder\FormBuilder;
use Throwable;

/**
 * What the panel gains from the form builder.
 *
 * A Filament panel is exactly the place where the interactive layouts of a form
 * — tabs, wizard, groups — are first class, so the `app` form target is enabled
 * here: without an SDK the builder only offers the universal palette, which is
 * what a public page can render. Each field also gains a `table` tab, so the
 * form says how its answers look in a listing, and the builder's own Preview of
 * an app form opens the panel's page instead of the HTML one.
 *
 * The toggle is the one field this SDK contributes to the palette: Filament has
 * one, and a form rendered as a Filament form can use it.
 */
class FormBuilderCapability implements Capability
{
    public function id(): string
    {
        return 'form-builder';
    }

    public function available(): bool
    {
        return app()->bound(FormBuilder::class);
    }

    public function apply(Panel $panel, TagixoFilamentPlugin $plugin): void
    {
        $forms = app(FormBuilder::class);

        if (($locked = $plugin->lockedFormTarget()) !== null) {
            // Locking to 'app' enables that target on its own.
            $forms->lockFormTarget($locked);
        } elseif ($plugin->appFormsEnabled()) {
            $forms->enableAppForms();
        }

        Tagixo::registerModule(ToggleModule::class);

        // How a field's answers appear in a Filament table, declared on the field.
        $forms->extendFormModule('*', ['table' => FilamentTablePropType::class]);
        $forms->extendFormModule(['checkbox', 'toggle'], ['table' => BooleanFilamentTablePropType::class]);
        $forms->extendFormModule(['date-picker'], ['table' => DateFilamentTablePropType::class]);
        $forms->extendFormModule(['file-upload'], ['table' => FileFilamentTablePropType::class]);

        // A panel form is sized by its layout, not by the field.
        $forms->hideFormModulePropTypes('*', ['sizing']);

        $forms->registerAppFormPreviewer(static function (int|string $id): ?string {
            try {
                return FormResource::getUrl('preview-app', ['record' => $id]);
            } catch (Throwable) {
                // No panel route for forms (the type is not administered here).
                return null;
            }
        });
    }
}
