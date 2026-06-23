<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Concerns\PersistsMenuItems;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Support\MenuTreeStructure;
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

        // Persistence stores a nested tree; the tree field edits a flat list
        // with per-item depth. Flatten on the way in.
        $data['items'] = MenuTreeStructure::treeToFlat($this->menuItemsToTree($record));

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $record->update($data);

        // Rebuild the nested tree from the flat (depth-carrying) editor state
        // before handing it to the persistence layer.
        $this->persistMenuItems($record, MenuTreeStructure::flatToTree(is_array($items) ? $items : []));

        return $record;
    }
}
