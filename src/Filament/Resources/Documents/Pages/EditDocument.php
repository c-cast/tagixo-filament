<?php

namespace Ccast\TagixoFilament\Filament\Resources\Documents\Pages;

use Ccast\TagixoFilament\Filament\Actions\DocumentPreviewAction;
use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(DocumentResource::class),

            DocumentPreviewAction::make(),

            DeleteAction::make(),
        ];
    }
}
