<?php

namespace Tagixo\Filament\Capabilities;

use Filament\Panel;
use Tagixo\Filament\Pages\ThemeBuilder;
use Tagixo\Filament\Resources\Menus\MenuResource;
use Tagixo\Filament\TagixoFilamentPlugin;
use Tagixo\PageBuilder\PageBuilder;

/**
 * What the panel gains from the page builder beyond its record types: the Theme
 * Builder, where a template is dressed zone by zone, and the menus of the site.
 *
 * The layouts themselves are a record type (`layouts`), so they are administered
 * like everything else.
 */
class PageBuilderCapability implements Capability
{
    public function id(): string
    {
        return 'page-builder';
    }

    public function available(): bool
    {
        return app()->bound(PageBuilder::class);
    }

    public function apply(Panel $panel, TagixoFilamentPlugin $plugin): void
    {
        if ($plugin->menusEnabled()) {
            $panel->resources([...$panel->getResources(), MenuResource::class]);
        }

        if ($plugin->themeBuilderEnabled()) {
            $panel->pages([...$panel->getPages(), ThemeBuilder::class]);
        }
    }
}
