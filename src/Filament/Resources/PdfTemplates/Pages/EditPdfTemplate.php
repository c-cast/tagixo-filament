<?php

namespace Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages;

use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\PdfTemplateResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPdfTemplate extends EditRecord
{
    protected static string $resource = PdfTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn (): string => PdfTemplateResource::getUrl('build', ['record' => $this->record])),

            DeleteAction::make(),
        ];
    }
}
