<?php

/**
 * Shape tests for the Menus resource that ships with `ccast/tagixo-filament`:
 * model wiring, page registry, the custom MenuTreeField, and the persistence
 * trait — without booting a full Filament panel (mirrors the resource-bridge
 * test style). The flat↔nested logic is covered by MenuTreeStructureTest.
 */

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Concerns\PersistsMenuItems;
use Ccast\TagixoFilament\Filament\Resources\Menus\Forms\MenuTreeField;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Filament\Resources\Menus\Pages\CreateMenu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Pages\EditMenu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Pages\ListMenus;
use Filament\Forms\Components\Field;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;

describe('MenuResource (Filament)', function () {
    it('extends Filament Resource and targets the core Menu model', function () {
        expect(is_subclass_of(MenuResource::class, Resource::class))->toBeTrue();
        expect(MenuResource::getModel())->toBe(Menu::class);
    });

    it('registers index, create and edit pages', function () {
        $pages = array_keys(MenuResource::getPages());

        expect($pages)->toContain('index', 'create', 'edit');
    });

    it('wires pages to the right Filament base classes', function () {
        expect(is_subclass_of(ListMenus::class, ListRecords::class))->toBeTrue();
        expect(is_subclass_of(CreateMenu::class, CreateRecord::class))->toBeTrue();
        expect(is_subclass_of(EditMenu::class, EditRecord::class))->toBeTrue();
    });

    it('uses the PersistsMenuItems trait on the create and edit pages', function () {
        expect(in_array(PersistsMenuItems::class, class_uses_recursive(CreateMenu::class), true))->toBeTrue();
        expect(in_array(PersistsMenuItems::class, class_uses_recursive(EditMenu::class), true))->toBeTrue();
    });
});

describe('MenuTreeField', function () {
    it('is a Filament field bound to the menu-tree view', function () {
        $field = MenuTreeField::make('items');

        expect($field)->toBeInstanceOf(Field::class);
        expect($field->getView())->toBe('tagixo-filament::filament.resources.menus.menu-tree');
    });

    it('exposes link-type and blank-item helpers to the view', function () {
        $field = MenuTreeField::make('items');

        $linkTypes = collect($field->getLinkTypeOptions());
        expect($linkTypes->pluck('value'))->toContain('url', 'page');
        expect($linkTypes->first())->toHaveKeys(['value', 'label']);

        expect($field->getBlankItem())->toHaveKey('depth');
    });
});
