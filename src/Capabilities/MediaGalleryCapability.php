<?php

namespace Tagixo\Filament\Capabilities;

use Filament\Panel;
use Tagixo\Core\MediaGallery\Services\MediaService;
use Tagixo\Filament\Pages\MediaGallery;
use Tagixo\Filament\TagixoFilamentPlugin;

/**
 * The media library of the core, in the panel: a page to browse and edit it, and
 * a picker available to any Filament form (`Forms\Fields\MediaPickerField`).
 *
 * The picker is this SDK's own, written in Alpine. The core also ships one, but
 * that one is a Vue component registered on a LiVue app — it is what the Primix
 * SDK uses, and in a panel without LiVue the browser cannot even load it.
 */
class MediaGalleryCapability implements Capability
{
    public function id(): string
    {
        return 'media-gallery';
    }

    public function available(): bool
    {
        return app()->bound(MediaService::class);
    }

    public function apply(Panel $panel, TagixoFilamentPlugin $plugin): void
    {
        if ($plugin->mediaLibraryEnabled()) {
            $panel->pages([...$panel->getPages(), MediaGallery::class]);
        }
    }
}
