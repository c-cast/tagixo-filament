<?php

namespace Ccast\TagixoFilament\Filament\Resources\Popups\Pages;

use Ccast\TagixoFilament\Filament\Resources\Popups\PopupResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPopup extends EditRecord
{
    protected static string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn (): string => PopupResource::getUrl('build', ['record' => $this->record])),

            DeleteAction::make(),
        ];
    }
}
