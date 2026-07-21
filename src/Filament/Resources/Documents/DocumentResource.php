<?php

namespace Ccast\TagixoFilament\Filament\Resources\Documents;

use Ccast\Tagixo\Models\DocumentTemplate;
use Ccast\TagixoFilament\Filament\Resources\Documents\Pages\BuildDocument;
use Ccast\TagixoFilament\Filament\Resources\Documents\Pages\CreateDocument;
use Ccast\TagixoFilament\Filament\Resources\Documents\Pages\EditDocument;
use Ccast\TagixoFilament\Filament\Resources\Documents\Pages\ListDocuments;
use Ccast\TagixoFilament\Filament\Resources\Documents\Schemas\DocumentForm;
use Ccast\TagixoFilament\Filament\Resources\Documents\Tables\DocumentsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DocumentResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 7;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function getModelLabel(): string
    {
        return __('Document');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Documents');
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
            'build' => BuildDocument::route('/{record}/build'),
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
