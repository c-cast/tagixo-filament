<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\Page;
use Ccast\Tagixo\Services\MenuItemsTreePersister;
use Ccast\Tagixo\Support\MenuTreeStructure;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMenu extends EditRecord
{
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
        $tree = app(MenuItemsTreePersister::class)->toTree($record);
        $tree = $this->restorePageIds($tree);
        $data['items'] = MenuTreeStructure::treeToFlat($tree);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $record->update($data);

        $tree = MenuTreeStructure::flatToTree(is_array($items) ? $items : []);
        $tree = $this->foldPageIds($tree);
        app(MenuItemsTreePersister::class)->persist($record, $tree);

        return $record;
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
