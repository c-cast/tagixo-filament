<?php

namespace Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages;

use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\PdfTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPdfTemplates extends ListRecords
{
    protected static string $resource = PdfTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
