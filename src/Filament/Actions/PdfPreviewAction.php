<?php

namespace Ccast\TagixoFilament\Filament\Actions;

use Ccast\Tagixo\Models\PdfTemplate;
use Filament\Actions\Action;
use Illuminate\Support\Facades\URL;

/**
 * Reusable "Preview PDF" action for the PdfTemplate resource (list + edit).
 *
 * Opens the core signed preview-pdf route in a new tab. That endpoint renders
 * the template to print-ready HTML and, when a PDF engine (dompdf) is
 * installed, streams a real inline PDF — so the user sees the generated file.
 * The record is injected by Filament in both the table and edit-page contexts.
 */
class PdfPreviewAction
{
    public static function make(): Action
    {
        return Action::make('previewPdf')
            ->label(__('Preview PDF'))
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->url(fn (PdfTemplate $record): string => URL::temporarySignedRoute(
                'tagixo.builder.preview-pdf',
                now()->addMinutes(5),
                ['id' => $record->id, '_preview' => 1],
            ))
            ->openUrlInNewTab()
            ->tooltip(__('Generate and open the PDF in a new tab'));
    }
}
