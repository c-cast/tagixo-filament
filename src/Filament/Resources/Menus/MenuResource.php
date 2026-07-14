<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus;

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Pages\CreateMenu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Pages\EditMenu;
use Ccast\TagixoFilament\Filament\Resources\Menus\Pages\ListMenus;
use Ccast\TagixoFilament\Filament\Resources\Menus\Schemas\MenuForm;
use Ccast\TagixoFilament\Filament\Resources\Menus\Tables\MenusTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 20;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-bars-3';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function getModelLabel(): string
    {
        return __('Menu');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Menus');
    }

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'edit' => EditMenu::route('/{record}/edit'),
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
