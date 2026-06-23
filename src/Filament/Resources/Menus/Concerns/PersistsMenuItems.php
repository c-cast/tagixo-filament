<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Concerns;

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\MenuItem;
use Ccast\Tagixo\Models\Page;
use Ccast\TagixoFilament\Support\MenuTreeStructure;
use Illuminate\Support\Collection;

/**
 * Persists a nested menu tree to / reads it from `tgx_menu_items`.
 *
 * Mirrors the Primix SDK trait so both admin SDKs share the same persistence
 * contract (the nested `children` shape). The flat↔nested conversion the tree
 * field needs lives in {@see MenuTreeStructure}
 * and is applied by the Create/Edit pages at the boundary.
 */
trait PersistsMenuItems
{
    protected function persistMenuItems(Menu $menu, array $items): void
    {
        $menu->allItems()->delete();
        $this->createMenuItemsRecursive($menu, $items, null);
    }

    protected function createMenuItemsRecursive(Menu $menu, array $items, ?int $parentId): void
    {
        $order = 0;

        foreach ($items as $item) {
            $children = $item['children'] ?? [];
            $label = $item['label'] ?? null;

            if (! $label) {
                continue;
            }

            $targetType = $item['target_type'] ?? 'url';

            // The "Page" link type is authored through a dedicated page picker
            // (target_page_id). Fold its selection into target_value, which is
            // the single column MenuItem persists / resolves.
            $targetValue = $item['target_value'] ?? null;
            if ($targetType === 'page' && ! empty($item['target_page_id'])) {
                $targetValue = $item['target_page_id'];
            }

            $payload = [
                'label' => $label,
                'target_type' => $targetType,
                'target_value' => $targetValue,
                'target_meta' => $item['target_meta'] ?? null,
                'new_tab' => (bool) ($item['new_tab'] ?? false),
                'icon' => $item['icon'] ?? null,
                'css_class' => $item['css_class'] ?? null,
                'visible' => (bool) ($item['visible'] ?? true),
                'order' => $order,
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
            ];

            $menuItem = MenuItem::create($payload);
            $this->createMenuItemsRecursive($menu, is_array($children) ? $children : [], $menuItem->id);
            $order++;
        }
    }

    protected function menuItemsToTree(Menu $menu): array
    {
        $items = $menu->allItems()->get();
        $grouped = $items->groupBy('parent_id');

        return $this->buildSubtree($grouped, null);
    }

    private function buildSubtree(Collection $grouped, ?int $parentId): array
    {
        $items = $grouped->get($parentId, collect())->sortBy('order');

        return $items->map(function (MenuItem $item) use ($grouped) {
            $targetType = $item->target_type instanceof \BackedEnum
                ? $item->target_type->value
                : (string) $item->target_type;

            // Pre-select the page picker for "Page" items. target_value may be a
            // numeric id or a slug — resolve both back to the page id the
            // picker uses as its option value.
            $targetPageId = null;
            if ($targetType === 'page' && $item->target_value) {
                $targetPageId = is_numeric($item->target_value)
                    ? (int) $item->target_value
                    : Page::where('slug', $item->target_value)->value('id');
            }

            return [
                'label' => $item->label,
                'target_type' => $targetType,
                'target_page_id' => $targetPageId,
                'target_value' => $item->target_value,
                'target_meta' => $item->target_meta,
                'new_tab' => (bool) $item->new_tab,
                'icon' => $item->icon,
                'css_class' => $item->css_class,
                'visible' => (bool) $item->visible,
                'children' => $this->buildSubtree($grouped, $item->id),
            ];
        })->values()->toArray();
    }
}
