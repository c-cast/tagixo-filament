<?php

namespace Tagixo\Filament\Resources;

/**
 * Pages of the page builder. Everything it shows comes from PageType.
 */
class PageResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'pages';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
