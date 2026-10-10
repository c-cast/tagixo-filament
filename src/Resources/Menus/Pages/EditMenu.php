<?php

namespace Tagixo\Filament\Resources\Menus\Pages;

use Tagixo\PageBuilder\Models\Menu;
use Tagixo\PageBuilder\Support\MenuTreeStructure;
use Tagixo\Filament\Resources\Menus\Concerns\PersistsMenuItems;
use Tagixo\Filament\Resources\Menus\MenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMenu extends EditRecord
{
    use PersistsMenuItems;
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Menu $record */
        $record = $this->record;
        $tree = $this->menuItemsToTree($record);
        $data['items'] = MenuTreeStructure::treeToFlat($tree);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $record->update($data);

        $tree = MenuTreeStructure::flatToTree(is_array($items) ? $items : []);
        $this->persistMenuItems($record, $tree);

        return $record;
    }
}
