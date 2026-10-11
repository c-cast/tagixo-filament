<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tagixo\Filament\Pages\ThemeBuilder;
use Tagixo\Filament\Resources\LayoutResource;
use Tagixo\Filament\TagixoFilamentPlugin;
use Tagixo\PageBuilder\Facades\PageBuilder;
use Tagixo\PageBuilder\Models\Layout;

uses(RefreshDatabase::class);

/*
 * A template dressed zone by zone. The header and the footer are the template's
 * own documents; the body belongs to a page, and for a template scoped to a
 * model that page is created on the way to the editor.
 */

it('carries the route the body of a model template goes through', function () {
    expect(Route::has('tagixo-filament.templates.body'))->toBeTrue();
});

it('shows the three zones of a template, and where each one is edited', function () {
    $layout = Layout::create(['name' => 'Shop', 'conditions' => [['type' => 'all_pages']]]);

    $zones = collect(app(ThemeBuilder::class)->zones($layout))->keyBy('scope');

    expect($zones->keys()->all())->toBe(['header', 'body', 'footer'])
        // A header is the template's own document.
        ->and($zones['header']['url'])->toContain('type=layouts')
        ->and($zones['header']['url'])->toContain('scope=header')
        ->and($zones['header']['configured'])->toBeFalse()
        ->and($zones['footer']['url'])->toContain('scope=footer')
        // The body of an ordinary template belongs to each page.
        ->and($zones['body']['editable'])->toBeFalse()
        ->and($zones['body']['url'])->toBeNull();
});

it('says a zone is built once it holds something', function () {
    $layout = Layout::create([
        'name' => 'Shop',
        'header_content' => ['components' => [['id' => 'h']], 'body' => []],
        'header_rendered_html' => '<header>Shop</header>',
    ]);

    $zones = collect(app(ThemeBuilder::class)->zones($layout))->keyBy('scope');

    expect($zones['header']['configured'])->toBeTrue()
        ->and($zones['footer']['configured'])->toBeFalse();
});

it('sends the body of a model template through the route that creates its page', function () {
    $layout = Layout::create([
        'name' => 'Articles',
        'conditions' => [['type' => 'model_archive', 'model' => 'articles']],
    ]);

    $body = collect(app(ThemeBuilder::class)->zones($layout))->keyBy('scope')['body'];

    expect($body['editable'])->toBeTrue()
        ->and($body['url'])->toBe(route('tagixo-filament.templates.body', ['layout' => $layout->getKey()]))
        ->and($body['configured'])->toBeFalse();
});

it('reads the templates global first, and says what each claims', function () {
    Layout::create(['name' => 'Zebra', 'conditions' => [['type' => 'homepage']]]);
    $global = Layout::create(['name' => 'Alpha', 'is_global' => true, 'conditions' => []]);

    $page = app(ThemeBuilder::class);

    expect($page->templates()->pluck('name')->all())->toBe(['Alpha', 'Zebra'])
        // A global template with no condition is what everything else wears.
        ->and($page->conditionsSummary($global))->toBe(__('Everything else'));
});

it('opens one editor per document of a layout', function () {
    $actions = LayoutResource::sectionActions();

    expect($actions)->toHaveCount(2)
        ->and(array_map(fn ($action) => $action->getName(), $actions))->toBe(['header', 'footer']);
});

it('appears only when the panel asks for it', function () {
    $panel = Filament\Facades\Filament::getPanel('admin');
    $plugin = app(TagixoFilamentPlugin::class);

    app(Tagixo\Filament\Capabilities\PageBuilderCapability::class)->apply($panel, $plugin);
    expect($panel->getPages())->not->toContain(ThemeBuilder::class);

    app(Tagixo\Filament\Capabilities\PageBuilderCapability::class)->apply($panel, $plugin->withThemeBuilder());
    expect($panel->getPages())->toContain(ThemeBuilder::class);
});
