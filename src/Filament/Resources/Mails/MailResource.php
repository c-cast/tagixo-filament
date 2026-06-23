<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails;

use Ccast\Tagixo\Models\MailTemplate;
use Ccast\TagixoFilament\Filament\Resources\Mails\Pages\BuildMail;
use Ccast\TagixoFilament\Filament\Resources\Mails\Pages\CreateMail;
use Ccast\TagixoFilament\Filament\Resources\Mails\Pages\EditMail;
use Ccast\TagixoFilament\Filament\Resources\Mails\Pages\ListMails;
use Ccast\TagixoFilament\Filament\Resources\Mails\Schemas\MailForm;
use Ccast\TagixoFilament\Filament\Resources\Mails\Tables\MailsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MailResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 5;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-envelope';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Mail template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Mail templates');
    }

    public static function form(Schema $schema): Schema
    {
        return MailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMails::route('/'),
            'create' => CreateMail::route('/create'),
            'edit' => EditMail::route('/{record}/edit'),
            'build' => BuildMail::route('/{record}/build'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
