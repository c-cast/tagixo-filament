<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Concerns\PersistsMenuItems;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Support\MenuTreeStructure;
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
        // Items are MenuItem rows, not a Menu column — persist them separately
        // after rebuilding the nested tree from the flat (depth-carrying) state.
        $items = $data['items'] ?? [];
        unset($data['items']);

        /** @var Menu $menu */
        $menu = new Menu($data);
        $menu->save();

        $this->persistMenuItems($menu, MenuTreeStructure::flatToTree(is_array($items) ? $items : []));

        return $menu;
    }
}
