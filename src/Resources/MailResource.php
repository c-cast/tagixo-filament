<?php

namespace Tagixo\Filament\Resources;

/**
 * Mail templates of the mail builder.
 */
class MailResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'mails';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMails::route('/'),
            'create' => Pages\CreateMail::route('/create'),
            'edit' => Pages\EditMail::route('/{record}/edit'),
        ];
    }
}
