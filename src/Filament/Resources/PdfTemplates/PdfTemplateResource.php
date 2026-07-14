<?php

namespace Ccast\TagixoFilament\Filament\Resources\PdfTemplates;

use Ccast\Tagixo\Models\PdfTemplate;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages\BuildPdfTemplate;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages\CreatePdfTemplate;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages\EditPdfTemplate;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Pages\ListPdfTemplates;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Schemas\PdfTemplateForm;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Tables\PdfTemplatesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PdfTemplateResource extends Resource
{
    protected static ?string $model = PdfTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 7;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function getModelLabel(): string
    {
        return __('PDF template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('PDF templates');
    }

    public static function form(Schema $schema): Schema
    {
        return PdfTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PdfTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPdfTemplates::route('/'),
            'create' => CreatePdfTemplate::route('/create'),
            'edit' => EditPdfTemplate::route('/{record}/edit'),
            'build' => BuildPdfTemplate::route('/{record}/build'),
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
