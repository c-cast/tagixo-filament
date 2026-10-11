<?php

namespace Tagixo\Filament\Resources;

/**
 * Popups of the page builder.
 */
class PopupResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'popups';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPopups::route('/'),
            'create' => Pages\CreatePopup::route('/create'),
            'edit' => Pages\EditPopup::route('/{record}/edit'),
        ];
    }
}
