<?php

namespace Ccast\TagixoFilament\Filament\Resources\Sliders;

use Ccast\Tagixo\Models\Slider;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Pages\EditSlider;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Pages\ListSliders;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Schemas\SliderForm;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Tables\SlidersTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 4;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-rectangle-group';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function getModelLabel(): string
    {
        return __('Slider');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sliders');
    }

    public static function form(Schema $schema): Schema
    {
        return SliderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlidersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Panel pages are metadata-only. The visual slider builder lives at the
     * plugin route `/tagixo/sliders/{id}/edit` and is opened in a new tab
     * via header/row actions (see ListSliders + SlidersTable).
     */
    public static function getPages(): array
    {
        return [
            'index' => ListSliders::route('/'),
            'edit' => EditSlider::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
