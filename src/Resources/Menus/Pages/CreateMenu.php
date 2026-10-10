<?php

namespace Tagixo\Filament\Resources\Menus\Pages;

use Tagixo\PageBuilder\Models\Menu;
use Tagixo\PageBuilder\Support\MenuTreeStructure;
use Tagixo\Filament\Resources\Menus\Concerns\PersistsMenuItems;
use Tagixo\Filament\Resources\Menus\MenuResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMenu extends CreateRecord
{
    use PersistsMenuItems;
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
        $this->persistMenuItems($menu, $tree);

        return $menu;
    }
}
