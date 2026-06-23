<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails\Pages;

use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMail extends EditRecord
{
    protected static string $resource = MailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn (): string => MailResource::getUrl('build', ['record' => $this->record])),

            DeleteAction::make(),
        ];
    }
}
