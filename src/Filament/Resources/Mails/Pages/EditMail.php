<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails\Pages;

use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMail extends EditRecord
{
    protected static string $resource = MailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(MailResource::class),

            DeleteAction::make(),
        ];
    }
}
