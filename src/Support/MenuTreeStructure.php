<?php

namespace Ccast\TagixoFilament\Support;

use Ccast\TagixoFilament\Filament\Resources\Menus\Concerns\PersistsMenuItems;

/**
 * Converts between the two shapes a menu tree takes in the admin:
 *
 *  - NESTED  — what {@see PersistsMenuItems}
 *    reads/writes: each item carries a `children` array (arbitrary depth). This
 *    is the persistence contract.
 *
 *  - FLAT    — what the WordPress-style tree field edits in the form state:
 *    a single linear list where each item carries a `depth` (0 = top level).
 *    Reordering and indenting become trivial list operations.
 *
 * The conversion happens only at the page boundary (Create/Edit), so the
 * persistence layer never sees the flat shape.
 *
 * Mirrors the Primix SDK implementation (Ccast\TagixoPrimix\Support\MenuTreeStructure)
 * so both admin SDKs behave identically.
 */
class MenuTreeStructure
{
    /**
     * A blank menu item with every key the editor binds to present.
     *
     * @return array<string, mixed>
     */
    public static function blankItem(): array
    {
        return [
            'label' => '',
            'target_type' => 'url',
            'target_page_id' => null,
            'target_value' => '',
            'target_meta' => null,
            'new_tab' => false,
            'icon' => null,
            'css_class' => null,
            'visible' => true,
            'depth' => 0,
        ];
    }

    /**
     * Nested (children) → flat (depth). Recursive.
     *
     * @param  array<int, array<string, mixed>>  $tree
     * @return array<int, array<string, mixed>>
     */
    public static function treeToFlat(array $tree, int $depth = 0): array
    {
        $flat = [];

        foreach ($tree as $node) {
            if (! is_array($node)) {
                continue;
            }

            $children = $node['children'] ?? [];
            unset($node['children']);
            $node['depth'] = $depth;
            $flat[] = $node;

            if (is_array($children) && $children !== []) {
                $flat = array_merge($flat, self::treeToFlat($children, $depth + 1));
            }
        }

        return $flat;
    }

    /**
     * Flat (depth) → nested (children). Depths are normalized first so a
     * malformed list (e.g. a child dragged above every parent) can never
     * produce an orphaned subtree.
     *
     * @param  array<int, array<string, mixed>>  $flat
     * @return array<int, array<string, mixed>>
     */
    public static function flatToTree(array $flat): array
    {
        $flat = array_values(self::normalizeDepths($flat));

        $nodes = [];
        $childrenByParent = [];
        $depthStack = []; // depth => index of the last node seen at that depth

        foreach ($flat as $i => $item) {
            $depth = (int) ($item['depth'] ?? 0);
            unset($item['depth']);
            $item['children'] = [];
            $nodes[$i] = $item;

            $parent = $depth === 0 ? -1 : ($depthStack[$depth - 1] ?? -1);
            $childrenByParent[$parent][] = $i;

            $depthStack[$depth] = $i;
            foreach (array_keys($depthStack) as $d) {
                if ($d > $depth) {
                    unset($depthStack[$d]);
                }
            }
        }

        $build = function (int $parentKey) use (&$build, $nodes, $childrenByParent): array {
            $result = [];

            foreach ($childrenByParent[$parentKey] ?? [] as $i) {
                $node = $nodes[$i];
                $node['children'] = $build($i);
                $result[] = $node;
            }

            return $result;
        };

        return $build(-1);
    }

    /**
     * Clamp the depth of each item so the list is always a valid tree:
     * the first item is always depth 0, and any item is at most one level
     * deeper than the item before it.
     *
     * @param  array<int, array<string, mixed>>  $flat
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeDepths(array $flat): array
    {
        $prev = -1;
        $out = [];

        foreach (array_values($flat) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $depth = (int) ($item['depth'] ?? 0);

            if ($depth < 0) {
                $depth = 0;
            }

            if ($depth > $prev + 1) {
                $depth = $prev + 1;
            }

            $item['depth'] = $depth;
            $prev = $depth;
            $out[] = $item;
        }

        return $out;
    }
}
