<?php

namespace Ccast\TagixoFilament\Filament\Resources\Popups\Pages;

use Ccast\TagixoFilament\Filament\Resources\Popups\PopupResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePopup extends CreateRecord
{
    protected static string $resource = PopupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['content'] ??= ['components' => [], 'body' => []];

        return $data;
    }
}
