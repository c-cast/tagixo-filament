<?php

namespace Ccast\TagixoFilament\Filament\Resources\Forms\Pages;

use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::forRoute('builder.forms.edit'),

            DeleteAction::make(),
        ];
    }
}
