<?php

namespace Ccast\TagixoFilament\Filament\Resources\Forms\Pages;

use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn (): string => route('builder.forms.edit', $this->record->id))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
