<?php

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\Page;
use Ccast\TagixoFilament\Filament\Resources\Menus\Concerns\PersistsMenuItems;
use Ccast\TagixoFilament\Support\MenuTreeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function menuTreeHarness(): object
{
    return new class
    {
        use PersistsMenuItems;

        public function persist(Menu $menu, array $items): void
        {
            $this->persistMenuItems($menu, $items);
        }

        public function tree(Menu $menu): array
        {
            return $this->menuItemsToTree($menu);
        }
    };
}

it('round-trips a 4-level deep menu through persistence', function () {
    $menu = Menu::create(['name' => 'Deep', 'slug' => 'deep']);
    $harness = menuTreeHarness();

    $flat = [
        ['label' => 'L0', 'target_type' => 'url', 'target_value' => '/', 'visible' => true, 'depth' => 0],
        ['label' => 'L1', 'target_type' => 'url', 'target_value' => '/a', 'visible' => true, 'depth' => 1],
        ['label' => 'L2', 'target_type' => 'url', 'target_value' => '/a/b', 'visible' => true, 'depth' => 2],
        ['label' => 'L3', 'target_type' => 'url', 'target_value' => '/a/b/c', 'visible' => true, 'depth' => 3],
        ['label' => 'Sibling', 'target_type' => 'url', 'target_value' => '/s', 'visible' => true, 'depth' => 0],
    ];

    $harness->persist($menu, MenuTreeStructure::flatToTree($flat));

    expect($menu->allItems()->count())->toBe(5);

    $readFlat = MenuTreeStructure::treeToFlat($harness->tree($menu->fresh()));

    expect(array_column($readFlat, 'label'))->toBe(['L0', 'L1', 'L2', 'L3', 'Sibling']);
    expect(array_column($readFlat, 'depth'))->toBe([0, 1, 2, 3, 0]);
});

it('preserves the page-picker selection across a flat round-trip', function () {
    $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published', 'content' => []]);
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main']);
    $harness = menuTreeHarness();

    $flat = [
        ['label' => 'About', 'target_type' => 'page', 'target_page_id' => $page->id, 'visible' => true, 'depth' => 0],
    ];

    $harness->persist($menu, MenuTreeStructure::flatToTree($flat));

    $readFlat = MenuTreeStructure::treeToFlat($harness->tree($menu->fresh()));

    expect($readFlat[0]['target_type'])->toBe('page');
    expect($readFlat[0]['target_page_id'])->toBe($page->id);
});
