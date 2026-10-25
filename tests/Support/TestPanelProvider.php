<?php

namespace Tagixo\Filament\Tests\Support;

use Filament\Panel;
use Filament\PanelProvider;
use Tagixo\Filament\TagixoFilamentPlugin;

/**
 * A panel with nothing in it but this SDK: what the plugin registers is
 * therefore exactly what the installed builders bring.
 */
class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // A real panel has a way in, and the guest middleware asks the panel
            // for it when the application has no login route of its own.
            ->login()
            ->plugin(TagixoFilamentPlugin::make());
    }
}
