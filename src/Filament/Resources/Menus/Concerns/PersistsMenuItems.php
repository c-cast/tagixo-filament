<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Concerns;

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\Page;
use Ccast\Tagixo\Services\MenuItemsTreePersister;

trait PersistsMenuItems
{
    protected function persistMenuItems(Menu $menu, array $tree): void
    {
        app(MenuItemsTreePersister::class)->persist($menu, $this->foldPageIds($tree));
    }

    protected function menuItemsToTree(Menu $menu): array
    {
        return $this->restorePageIds(app(MenuItemsTreePersister::class)->toTree($menu));
    }

    private function foldPageIds(array $items): array
    {
        return array_map(function (array $item) {
            if (($item['target_type'] ?? null) === 'page' && ! empty($item['target_page_id'])) {
                $item['target_value'] = $item['target_page_id'];
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->foldPageIds($item['children']);
            }
            return $item;
        }, $items);
    }

    private function restorePageIds(array $items): array
    {
        return array_map(function (array $item) {
            if (($item['target_type'] ?? null) === 'page' && ! empty($item['target_value'])) {
                $item['target_page_id'] = is_numeric($item['target_value'])
                    ? (int) $item['target_value']
                    : Page::where('slug', $item['target_value'])->value('id');
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->restorePageIds($item['children']);
            }
            return $item;
        }, $items);
    }
}
