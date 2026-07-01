<?php

namespace Ccast\TagixoFilament\Filament\Resources\Popups\Pages;

use Ccast\TagixoFilament\Filament\Resources\Popups\PopupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPopups extends ListRecords
{
    protected static string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
