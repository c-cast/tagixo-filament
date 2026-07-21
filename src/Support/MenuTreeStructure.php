<?php

namespace Ccast\TagixoFilament\Support;

use Ccast\Tagixo\Support\MenuTreeStructure as TagixoMenuTreeStructure;

/**
 * Re-exports the plugin's MenuTreeStructure under the Filament SDK namespace
 * so consumer code can depend on a stable tagixo-filament class reference.
 *
 * @method static array blankItem()
 * @method static array treeToFlat(array $tree, int $depth = 0)
 * @method static array flatToTree(array $flat)
 * @method static array normalizeDepths(array $flat)
 */
class MenuTreeStructure extends TagixoMenuTreeStructure
{
    /**
     * Override: server-side conversions don't need the client-only `_key` field
     * that the Vue drag-drop UI uses for reactivity tracking.
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
            unset($node['children'], $node['_key']);
            $node['depth'] = $depth;
            $flat[] = $node;

            if (is_array($children) && $children !== []) {
                $flat = array_merge($flat, static::treeToFlat($children, $depth + 1));
            }
        }

        return $flat;
    }
}
