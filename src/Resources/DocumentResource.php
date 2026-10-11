<?php

namespace Tagixo\Filament\Resources;

/**
 * Documents of the document builder, printed to PDF by the engine the
 * application chose.
 */
class DocumentResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'documents';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
