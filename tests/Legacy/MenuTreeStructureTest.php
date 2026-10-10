<?php

use Ccast\TagixoFilament\Support\MenuTreeStructure;

describe('MenuTreeStructure conversions', function () {
    it('flattens a nested tree with correct depths', function () {
        $tree = [
            ['label' => 'A', 'children' => [
                ['label' => 'A1', 'children' => [
                    ['label' => 'A1a', 'children' => []],
                ]],
                ['label' => 'A2', 'children' => []],
            ]],
            ['label' => 'B', 'children' => []],
        ];

        $flat = MenuTreeStructure::treeToFlat($tree);

        expect(array_column($flat, 'label'))->toBe(['A', 'A1', 'A1a', 'A2', 'B']);
        expect(array_column($flat, 'depth'))->toBe([0, 1, 2, 1, 0]);
        expect($flat[0])->not->toHaveKey('children');
    });

    it('rebuilds a nested tree from a flat depth list (round-trip)', function () {
        $tree = [
            ['label' => 'A', 'children' => [
                ['label' => 'A1', 'children' => [
                    ['label' => 'A1a', 'children' => []],
                ]],
            ]],
            ['label' => 'B', 'children' => []],
        ];

        $rebuilt = MenuTreeStructure::flatToTree(MenuTreeStructure::treeToFlat($tree));

        expect($rebuilt)->toEqual($tree);
    });

    it('normalizes depths so the list is always a valid tree', function () {
        $flat = [
            ['label' => 'A', 'depth' => 5],   // first item → forced to 0
            ['label' => 'B', 'depth' => 3],   // jump from 0 → clamped to 1
            ['label' => 'C', 'depth' => -2],  // negative → 0
            ['label' => 'D', 'depth' => 2],   // from 0 → clamped to 1
        ];

        $depths = array_column(MenuTreeStructure::normalizeDepths($flat), 'depth');

        expect($depths)->toBe([0, 1, 0, 1]);
    });

    it('re-parents an orphaned child via normalization when building the tree', function () {
        $flat = [
            ['label' => 'orphan', 'depth' => 1],
            ['label' => 'root', 'depth' => 0],
        ];

        $tree = MenuTreeStructure::flatToTree($flat);

        expect($tree)->toHaveCount(2);
        expect($tree[0]['label'])->toBe('orphan');
        expect($tree[0]['children'])->toBe([]);
    });

    it('provides a blank item with every editor key', function () {
        $blank = MenuTreeStructure::blankItem();

        expect($blank)->toHaveKeys([
            'label', 'target_type', 'target_page_id', 'target_value',
            'target_meta', 'new_tab', 'icon', 'css_class', 'visible', 'depth',
        ]);
        expect($blank['depth'])->toBe(0);
        expect($blank['target_type'])->toBe('url');
        expect($blank['visible'])->toBeTrue();
    });
});
