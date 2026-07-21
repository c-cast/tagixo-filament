<?php

namespace Ccast\TagixoFilament\Filament\Actions;

use Ccast\Tagixo\Models\DocumentTemplate;
use Filament\Actions\Action;
use Illuminate\Support\Facades\URL;

/**
 * Reusable "Preview Document" action for the DocumentTemplate resource (list + edit).
 *
 * Opens the core signed preview-document route in a new tab. That endpoint renders
 * the template to print-ready HTML and, when a PDF engine (dompdf) is
 * installed, streams a real inline PDF — so the user sees the generated file.
 * The record is injected by Filament in both the table and edit-page contexts.
 */
class DocumentPreviewAction
{
    public static function make(): Action
    {
        return Action::make('previewDocument')
            ->label(__('Preview Document'))
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->url(fn (DocumentTemplate $record): string => URL::temporarySignedRoute(
                'tagixo.builder.preview-document',
                now()->addMinutes(5),
                ['id' => $record->id, '_preview' => 1],
            ))
            ->openUrlInNewTab()
            ->tooltip(__('Generate and open the document in a new tab'));
    }
}
