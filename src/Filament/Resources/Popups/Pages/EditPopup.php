<?php

namespace Ccast\TagixoFilament\Filament\Resources\Popups\Pages;

use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Popups\PopupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPopup extends EditRecord
{
    protected static string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(PopupResource::class),

            DeleteAction::make(),
        ];
    }
}
