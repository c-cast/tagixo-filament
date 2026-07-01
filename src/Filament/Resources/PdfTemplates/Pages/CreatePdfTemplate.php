<?php

namespace Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages;

use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\PdfTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfTemplate extends CreateRecord
{
    protected static string $resource = PdfTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
