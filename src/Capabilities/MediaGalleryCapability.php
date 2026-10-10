<?php

namespace Tagixo\Filament\Capabilities;

use Filament\Panel;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Tagixo\Core\MediaGallery\Services\MediaService;
use Tagixo\Core\Support\TagixoAsset;
use Tagixo\Filament\Pages\MediaGallery;
use Tagixo\Filament\TagixoFilamentPlugin;

/**
 * The media library of the core, in the panel: a page to browse and edit it, and
 * the picker of the editor available to any Filament form
 * (`Forms\Fields\MediaPickerField`).
 *
 * The picker's own script is the core's, published to public/vendor/tagixo/core:
 * it registers the component the field renders, so no builder bundle is needed
 * for a form to pick an image.
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

        FilamentAsset::register([
            Js::make('tagixo-media-picker', TagixoAsset::url('core/media-picker.js', version: false)),
        ], 'tagixo/filament');
    }
}
