<?php

namespace Tagixo\Filament;

use Illuminate\Support\ServiceProvider;

class TagixoFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tagixo-filament.php', 'tagixo-filament');

        // One instance: the panel configures the plugin fluently and the static
        // resources read that same configuration back.
        $this->app->singleton(TagixoFilamentPlugin::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tagixo-filament');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tagixo-filament');

        $this->publishes([
            __DIR__.'/../config/tagixo-filament.php' => config_path('tagixo-filament.php'),
        ], 'tagixo-filament-config');
    }
}
