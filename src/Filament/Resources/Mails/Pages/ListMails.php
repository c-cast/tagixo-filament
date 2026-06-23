<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails\Pages;

use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMails extends ListRecords
{
    protected static string $resource = MailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
