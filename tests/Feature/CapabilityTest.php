<?php

use Filament\Facades\Filament;
use Tagixo\Filament\Capabilities\MediaGalleryCapability;
use Tagixo\Filament\Capabilities\PageBuilderCapability;
use Tagixo\Filament\Pages\MediaGallery;
use Tagixo\Filament\Resources\Menus\MenuResource;
use Tagixo\Filament\TagixoFilamentPlugin;

/*
 * What a package brings beyond its record types: the media library of the core,
 * the menus of the page builder. Each capability answers for itself whether it
 * applies, asking the container rather than the autoloader.
 */

it('offers the capabilities of the packages that are installed', function () {
    $ids = array_map(
        fn ($capability) => $capability->id(),
        app(TagixoFilamentPlugin::class)->resolveCapabilities(),
    );

    expect($ids)->toBe(['form-builder', 'media-gallery', 'page-builder']);
});

it('drops a capability the panel refuses', function () {
    $plugin = app(TagixoFilamentPlugin::class)->withoutCapability('media-gallery');

    $ids = array_map(fn ($capability) => $capability->id(), $plugin->resolveCapabilities());

    expect($ids)->toBe(['form-builder', 'page-builder']);
});

it('asks the container, not the autoloader', function () {
    // Both classes are on disk here; what decides is whether the package they
    // belong to has booted and bound its service.
    expect(app(MediaGalleryCapability::class)->available())->toBeTrue()
        ->and(app(PageBuilderCapability::class)->available())->toBeTrue();
});

it('adds the media library only when the panel asks for it', function () {
    $panel = Filament::getPanel('admin');
    $plugin = app(TagixoFilamentPlugin::class);

    expect($panel->getPages())->not->toContain(MediaGallery::class);

    app(MediaGalleryCapability::class)->apply($panel, $plugin->withMediaGallery());

    expect($panel->getPages())->toContain(MediaGallery::class);
});

it('adds the menus only when the panel asks for them', function () {
    $panel = Filament::getPanel('admin');
    $plugin = app(TagixoFilamentPlugin::class);

    app(PageBuilderCapability::class)->apply($panel, $plugin);
    expect($panel->getResources())->not->toContain(MenuResource::class);

    app(PageBuilderCapability::class)->apply($panel, $plugin->withMenus());
    expect($panel->getResources())->toContain(MenuResource::class);
});
