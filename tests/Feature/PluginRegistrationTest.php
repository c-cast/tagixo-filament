<?php

use Tagixo\Core\BuilderTypeRegistry;
use Tagixo\Filament\Resources\DocumentResource;
use Tagixo\Filament\Resources\FormResource;
use Tagixo\Filament\Resources\PageResource;
use Tagixo\Filament\Resources\SliderResource;
use Tagixo\Filament\TagixoFilamentPlugin;

/*
 * What the panel offers comes from the BuilderTypeRegistry: the types of the
 * packages installed, and nothing for the builders that were not bought.
 */

it('gives an admin resource to every installed builder type, and only those', function () {
    $resources = app(TagixoFilamentPlugin::class)->resolveResources();

    // The slider builder is not among this package's dev dependencies, so its
    // type is not registered and its resource must not appear.
    expect(array_keys($resources))
        ->toBe(['pages', 'popups', 'global-blocks', 'layouts', 'forms', 'mails', 'documents'])
        ->and($resources)->not->toHaveKey('sliders')
        ->and($resources['pages'])->toBe(PageResource::class)
        ->and($resources['documents'])->toBe(DocumentResource::class);
});

it('drops a type the panel does not want', function () {
    $plugin = app(TagixoFilamentPlugin::class)->except(['mails', 'documents']);

    expect(array_keys($plugin->resolveResources()))
        ->toBe(['pages', 'popups', 'global-blocks', 'layouts', 'forms']);
});

it('keeps only the types the panel asked for, in that order', function () {
    $plugin = app(TagixoFilamentPlugin::class)->only(['forms', 'pages']);

    // `only` filters; the order is the one the plugin declares.
    expect(array_keys($plugin->resolveResources()))->toBe(['pages', 'forms']);
});

it('lets a panel replace the resource of a type with its own', function () {
    $plugin = app(TagixoFilamentPlugin::class)->resource('pages', FormResource::class);

    expect($plugin->resolveResources()['pages'])->toBe(FormResource::class);
});

it('says nothing about a type whose package is absent', function () {
    expect(app(BuilderTypeRegistry::class)->has('sliders'))->toBeFalse()
        // And asking that resource for its type is an error, not an empty page.
        ->and(fn () => SliderResource::builderType())->toThrow(RuntimeException::class);
});
