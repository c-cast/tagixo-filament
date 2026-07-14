<?php

namespace Ccast\TagixoFilament;

use Ccast\TagixoFilament\Console\Commands\MakeBuilderPageCommand;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentWrapperModule;
use Ccast\TagixoFilament\FormBuilder\FilamentModuleRegistry;
use Ccast\TagixoFilament\FormBuilder\Modules\CheckboxField;
use Ccast\TagixoFilament\FormBuilder\Modules\DatePickerField;
use Ccast\TagixoFilament\FormBuilder\Modules\FieldsetField;
use Ccast\TagixoFilament\FormBuilder\Modules\FileUploadField;
use Ccast\TagixoFilament\FormBuilder\Modules\GridField;
use Ccast\TagixoFilament\FormBuilder\Modules\GroupField;
use Ccast\TagixoFilament\FormBuilder\Modules\RadioField;
use Ccast\TagixoFilament\FormBuilder\Modules\SectionField;
use Ccast\TagixoFilament\FormBuilder\Modules\SelectField;
use Ccast\TagixoFilament\FormBuilder\Modules\SubmitButtonField;
use Ccast\TagixoFilament\FormBuilder\Modules\TabField;
use Ccast\TagixoFilament\FormBuilder\Modules\TabsField;
use Ccast\TagixoFilament\FormBuilder\Modules\TextAreaField;
use Ccast\TagixoFilament\FormBuilder\Modules\TextInputField;
use Ccast\TagixoFilament\FormBuilder\Modules\WizardField;
use Ccast\TagixoFilament\FormBuilder\Modules\WizardStepField;
use Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TagixoFilamentServiceProvider extends PackageServiceProvider
{
    public static string $name = 'tagixo-filament';

    public static string $viewNamespace = 'tagixo-filament';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasMigrations([
                '2026_03_27_000001_create_tagixo_filament_form_bindings_table',
                '2026_03_27_000002_rename_tagixo_filament_form_bindings_to_tgx_form_bindings',
            ])
            ->runsMigrations()
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                // Migrations are auto-loaded via runsMigrations(); publishing
                // them too would register duplicate copies with fresh timestamps.
                $command
                    ->publishConfigFile()
                    ->askToRunMigrations();
            });
    }

    public function packageRegistered(): void
    {
        $defaultFields = [
            'text' => TextInputField::class,
            'textarea' => TextAreaField::class,
            'select' => SelectField::class,
            'checkbox' => CheckboxField::class,
            'radio' => RadioField::class,
            'date' => DatePickerField::class,
            'file' => FileUploadField::class,
            'submit' => SubmitButtonField::class,
        ];
        $defaultWrappers = [
            'grid' => GridField::class,
            'section' => SectionField::class,
            'fieldset' => FieldsetField::class,
            'group' => GroupField::class,
            'tabs' => TabsField::class,
            'tab' => TabField::class,
            'wizard' => WizardField::class,
            'wizard-step' => WizardStepField::class,
        ];

        $configuredFields = $this->normalizeMappings(
            config('tagixo-filament.form_mapper.fields', []),
            FilamentFieldModule::class,
        );
        $configuredWrappers = $this->normalizeMappings(
            config('tagixo-filament.form_mapper.wrappers', []),
            FilamentWrapperModule::class,
        );

        $this->app->singleton(FilamentModuleRegistry::class, function () use ($defaultFields, $defaultWrappers, $configuredFields, $configuredWrappers) {
            return (new FilamentModuleRegistry)->registerMany(
                fields: array_merge($defaultFields, $configuredFields),
                wrappers: array_merge($defaultWrappers, $configuredWrappers),
            );
        });

        $this->app->singleton(ReactivityFunctionRegistry::class, static fn () => new ReactivityFunctionRegistry);
    }

    public function packageBooted(): void
    {
        // Optional per-model policies from config: lets the host app gate each
        // Tagixo builder — resource visibility/access follows the model's
        // viewAny(), record operations follow the matching ability. Models
        // without an entry keep the historical behaviour (open to any panel
        // user), since Filament allows actions when no policy is registered.
        foreach ((array) config('tagixo-filament.policies', []) as $model => $policy) {
            if (is_string($model) && is_string($policy) && class_exists($model) && class_exists($policy)) {
                Gate::policy($model, $policy);
            }
        }

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tagixo-filament');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/tagixo-filament'),
        ], 'tagixo-filament-translations');

        $this->loadViewsFrom(__DIR__.'/../resources/views/media-gallery', 'media-gallery');

        // Filament is an 'app' form runtime: enable the app target and render its
        // previews as real Filament forms (native interactive Tabs/Wizard) via our
        // resource preview page.
        if (class_exists(\Ccast\Tagixo\Tagixo::class)) {
            $tagixo = app(\Ccast\Tagixo\Tagixo::class);
            $tagixo->enableAppForms();
            $tagixo->registerAppFormPreviewer(function (int|string $id): ?string {
                try {
                    return \Ccast\TagixoFilament\Filament\Resources\Forms\FormResource::getUrl(
                        'preview-app',
                        ['record' => $id],
                    );
                } catch (\Throwable) {
                    return null;
                }
            });
        }

        // Use the pre-built CSS from the core package's dist/ (committed, ships via Composer).
        // Filament copies it to public/css/ccast/tagixo-filament/tagixo.css on `filament:assets`.
        FilamentAsset::register([
            Css::make('tagixo', base_path('vendor/ccast/tagixo/dist/tagixo.css')),
        ], $this->getAssetPackageName());

    }

    protected function getAssetPackageName(): ?string
    {
        return 'ccast/tagixo-filament';
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            MakeBuilderPageCommand::class,
        ];
    }

    /**
     * @return array<string, class-string>
     */
    protected function normalizeMappings(mixed $mappings, string $contract): array
    {
        if (! is_array($mappings)) {
            return [];
        }

        $normalized = [];

        foreach ($mappings as $type => $class) {
            if (! is_string($type) || trim($type) === '') {
                continue;
            }

            if (
                ! is_string($class) ||
                ! class_exists($class) ||
                ! is_subclass_of($class, $contract)
            ) {
                continue;
            }

            $normalized[trim($type)] = $class;
        }

        return $normalized;
    }
}
