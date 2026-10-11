<?php

namespace Tagixo\Filament\Resources;

/**
 * Blocks saved once and referenced across pages.
 */
class GlobalBlockResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'global-blocks';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlobalBlocks::route('/'),
            'create' => Pages\CreateGlobalBlock::route('/create'),
            'edit' => Pages\EditGlobalBlock::route('/{record}/edit'),
        ];
    }
}
