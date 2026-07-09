<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages;

use Ccast\Tagixo\Models\Page;
use Ccast\TagixoFilament\Filament\Resources\Pages\Pages\BuildPage;
use Ccast\TagixoFilament\Filament\Resources\Pages\Pages\CreatePage;
use Ccast\TagixoFilament\Filament\Resources\Pages\Pages\EditPage;
use Ccast\TagixoFilament\Filament\Resources\Pages\Pages\ListPages;
use Ccast\TagixoFilament\Filament\Resources\Pages\Schemas\PageForm;
use Ccast\TagixoFilament\Filament\Resources\Pages\Tables\PagesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function getEloquentQuery(): Builder
    {
        // No global userManaged() scope here: the List page splits records into
        // "Pages" (user-managed) and "Model templates" (source-synced archive /
        // single pages) tabs, so template pages become editable in the builder.
        return parent::getEloquentQuery();
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pages');
    }

    public static function form(Schema $schema): Schema
    {
        return PageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
            'build' => BuildPage::route('/{record}/build'),
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
