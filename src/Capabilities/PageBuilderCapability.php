<?php

namespace Tagixo\Filament\Capabilities;

use Filament\Panel;
use Tagixo\Filament\Resources\Menus\MenuResource;
use Tagixo\Filament\TagixoFilamentPlugin;
use Tagixo\PageBuilder\PageBuilder;

/**
 * What the panel gains from the page builder beyond its record types: the menus
 * of the site, and the screens no type could provide.
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
    }
}
