<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Services\MenuItemsTreePersister;
use Ccast\Tagixo\Support\MenuTreeStructure;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        /** @var Menu $menu */
        $menu = new Menu($data);
        $menu->save();

        $tree = MenuTreeStructure::flatToTree(is_array($items) ? $items : []);
        $tree = $this->foldPageIds($tree);
        app(MenuItemsTreePersister::class)->persist($menu, $tree);

        return $menu;
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
}
