<?php

namespace Tagixo\Filament\Resources;

/**
 * Forms of the form builder.
 */
class FormResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'forms';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
            // The builder's own Preview of an app form opens this instead of
            // the HTML one: a Filament form is what that form will really be.
            'preview-app' => Pages\PreviewAppForm::route('/{record}/preview'),
        ];
    }
}
