<?php

namespace Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages;

use Ccast\TagixoFilament\Filament\Actions\PdfPreviewAction;
use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\PdfTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPdfTemplate extends EditRecord
{
    protected static string $resource = PdfTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(PdfTemplateResource::class),

            PdfPreviewAction::make(),

            DeleteAction::make(),
        ];
    }
}
