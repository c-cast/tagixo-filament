<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails\Pages;

use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMail extends CreateRecord
{
    protected static string $resource = MailResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
