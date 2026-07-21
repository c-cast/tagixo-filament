<?php

namespace Ccast\TagixoFilament\Filament\Resources\Forms;

use Ccast\Tagixo\Models\FormSchema;
use Ccast\Tagixo\Tagixo;
use Ccast\TagixoFilament\Filament\Resources\Forms\Pages\EditForm;
use Ccast\TagixoFilament\Filament\Resources\Forms\Pages\ListForms;
use Ccast\TagixoFilament\Filament\Resources\Forms\Pages\PreviewAppForm;
use Ccast\TagixoFilament\Filament\Resources\Forms\Schemas\FormForm;
use Ccast\TagixoFilament\Filament\Resources\Forms\Tables\FormsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FormResource extends Resource
{
    protected static ?string $model = FormSchema::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 3;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Visual Builder');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $locked = app(Tagixo::class)->getLockedFormTarget();

        if ($locked !== null) {
            $query->where('form_target', $locked);
        }

        return $query;
    }

    public static function getModelLabel(): string
    {
        return __('Form');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Forms');
    }

    public static function form(Schema $schema): Schema
    {
        return FormForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Panel pages are metadata-only. The actual visual form builder lives at
     * the plugin route `/tagixo/forms/{id}/edit` and is opened in a new tab
     * via header/row actions (see ListForms + FormsTable).
     */
    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'edit' => EditForm::route('/{record}/edit'),
            // Standalone preview of an app-target form, rendered as a real Filament
            // form (native Tabs/Wizard). Tagixo core delegates here for app forms.
            'preview-app' => PreviewAppForm::route('/{record}/preview-app'),
        ];
    }
}
