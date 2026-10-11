<?php

namespace Tagixo\Filament;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Tagixo\Filament\FormBuilder\Contracts\FilamentFieldModule;
use Tagixo\Filament\FormBuilder\Contracts\FilamentWrapperModule;
use Tagixo\Filament\FormBuilder\FilamentModuleRegistry;
use Tagixo\Filament\FormBuilder\Modules;

class TagixoFilamentServiceProvider extends ServiceProvider
{
    /**
     * A Filament field per form module the builder offers. The keys are the
     * **field types** — the vocabulary a saved schema speaks (`text`, `grid`),
     * not the module ids (`text-input`, `form-grid`): a key that matches
     * nothing silently drops the field from the rendered form, which is why
     * FormModuleCoverageTest exists.
     *
     * @var array<string, class-string<FilamentFieldModule>>
     */
    public const FIELDS = [
        'text' => Modules\TextInputField::class,
        'textarea' => Modules\TextAreaField::class,
        'select' => Modules\SelectField::class,
        'checkbox' => Modules\CheckboxField::class,
        'toggle' => Modules\ToggleField::class,
        'radio' => Modules\RadioField::class,
        'date' => Modules\DatePickerField::class,
        'file' => Modules\FileUploadField::class,
        'submit' => Modules\SubmitButtonField::class,
    ];

    /**
     * And a Filament layout component per wrapper module, keyed the same way.
     *
     * @var array<string, class-string<FilamentWrapperModule>>
     */
    public const WRAPPERS = [
        'grid' => Modules\GridField::class,
        'section' => Modules\SectionField::class,
        'fieldset' => Modules\FieldsetField::class,
        'group' => Modules\GroupField::class,
        'tabs' => Modules\TabsField::class,
        'tab' => Modules\TabField::class,
        'wizard' => Modules\WizardField::class,
        'wizard-step' => Modules\WizardStepField::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tagixo-filament.php', 'tagixo-filament');

        // One instance: the panel configures the plugin fluently and the static
        // resources read that same configuration back.
        $this->app->singleton(TagixoFilamentPlugin::class);

        $this->app->singleton(FilamentModuleRegistry::class, fn (): FilamentModuleRegistry => (new FilamentModuleRegistry)->registerMany(
            fields: [
                ...static::FIELDS,
                ...$this->configured('tagixo-filament.form_mapper.fields', FilamentFieldModule::class),
            ],
            wrappers: [
                ...static::WRAPPERS,
                ...$this->configured('tagixo-filament.form_mapper.wrappers', FilamentWrapperModule::class),
            ],
        ));

    }

    public function boot(): void
    {
        // The bindings of a form to what renders it are this SDK's own table.
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tagixo-filament');
        $this->loadRoutesFrom(__DIR__.'/../routes/tagixo-filament.php');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tagixo-filament');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        // Per-model policies from config: an application gates each builder, and
        // a model without an entry keeps Filament's own default.
        foreach ((array) config('tagixo-filament.policies', []) as $model => $policy) {
            if (is_string($model) && is_string($policy) && class_exists($model) && class_exists($policy)) {
                Gate::policy($model, $policy);
            }
        }

        $this->publishes([
            __DIR__.'/../config/tagixo-filament.php' => config_path('tagixo-filament.php'),
        ], 'tagixo-filament-config');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/tagixo-filament'),
        ], 'tagixo-filament-translations');
    }

    /**
     * Mappings an application adds in config, keeping only the ones that are
     * really a module of the right kind.
     *
     * @return array<string, class-string>
     */
    protected function configured(string $key, string $contract): array
    {
        $mappings = [];

        foreach ((array) config($key, []) as $type => $class) {
            if (is_string($type) && is_string($class) && class_exists($class) && is_subclass_of($class, $contract)) {
                $mappings[$type] = $class;
            }
        }

        return $mappings;
    }
}
