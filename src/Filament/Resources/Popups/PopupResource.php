<?php

namespace Ccast\TagixoFilament\Filament\Resources\Popups;

use Ccast\Tagixo\Models\Popup;
use Ccast\TagixoFilament\Filament\Resources\Popups\Pages\BuildPopup;
use Ccast\TagixoFilament\Filament\Resources\Popups\Pages\CreatePopup;
use Ccast\TagixoFilament\Filament\Resources\Popups\Pages\EditPopup;
use Ccast\TagixoFilament\Filament\Resources\Popups\Pages\ListPopups;
use Ccast\TagixoFilament\Filament\Resources\Popups\Schemas\PopupForm;
use Ccast\TagixoFilament\Filament\Resources\Popups\Tables\PopupsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 6;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-window';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function getModelLabel(): string
    {
        return __('Popup');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Popups');
    }

    public static function form(Schema $schema): Schema
    {
        return PopupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PopupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPopups::route('/'),
            'create' => CreatePopup::route('/create'),
            'edit' => EditPopup::route('/{record}/edit'),
            'build' => BuildPopup::route('/{record}/build'),
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
