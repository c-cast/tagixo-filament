<?php

namespace Tagixo\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Tagixo\Core\Builder\Types\RecordField;
use Tagixo\Core\BuilderTypeRegistry;
use Tagixo\Core\Contracts\BuilderTypeContract;
use Tagixo\Filament\Support\RecordFieldSchema;
use Tagixo\Filament\TagixoFilamentPlugin;
use UnitEnum;

/**
 * One admin resource for one Tagixo record type, built entirely from what the
 * type declares: model, listing query, columns and metadata inputs come from
 * `BuilderTypeContract`, and every builder installed gets the same treatment.
 *
 * A subclass only names its type (`$tagixoType`), because Filament keys
 * resources and their routes by class. Everything else belongs here.
 */
abstract class TagixoRecordResource extends Resource
{
    /**
     * Key of the builder type in the BuilderTypeRegistry ('pages', 'mails', …).
     */
    protected static string $tagixoType = '';

    public static function tagixoType(): string
    {
        return static::$tagixoType;
    }

    /**
     * The type handler. Absent means the package that registers it is not
     * installed, and the plugin never registers this resource.
     */
    public static function builderType(): BuilderTypeContract
    {
        $key = static::tagixoType();

        return app(BuilderTypeRegistry::class)->get($key)
            ?? throw new RuntimeException("No Tagixo builder type registered for [{$key}].");
    }

    /**
     * @return list<RecordField>
     */
    public static function tagixoFields(): array
    {
        return static::builderType()->fields();
    }

    public static function getModel(): string
    {
        return static::builderType()->modelClass();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return static::tagixoType();
    }

    public static function getNavigationLabel(): string
    {
        return static::builderType()->label();
    }

    public static function getPluralModelLabel(): string
    {
        return static::builderType()->label();
    }

    public static function getModelLabel(): string
    {
        return Str::singular(static::builderType()->label());
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return app(TagixoFilamentPlugin::class)->iconFor(static::tagixoType());
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return app(TagixoFilamentPlugin::class)->getNavigationGroup();
    }

    /**
     * The attribute Filament shows as a record's own name, taken from the first
     * field the type declares.
     */
    public static function getRecordTitleAttribute(): ?string
    {
        return RecordFieldSchema::titleAttribute(static::tagixoFields());
    }

    public static function getEloquentQuery(): Builder
    {
        return static::builderType()->indexQuery(request());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(RecordFieldSchema::columns(static::tagixoFields()))
            // Clicking a row opens what the record is for: its builder.
            ->recordUrl(static fn (Model $record): string => static::builderUrl($record))
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions(array_values(array_filter([
                static::buildAction(),
                static::previewAction(),
                EditAction::make(),
                DeleteAction::make(),
            ])))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(RecordFieldSchema::inputs(
            static::tagixoFields(),
            array_keys(static::builderType()->storeRules()),
        ));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTagixoRecords::route('/'),
            'create' => Pages\CreateTagixoRecord::route('/create'),
            'edit' => Pages\EditTagixoRecord::route('/{record}/edit'),
        ];
    }

    /**
     * The record's attributes as the form wants them. A resource whose form asks
     * its questions differently from the way the record stores them (the
     * conditions of a layout) translates here, and back in `dataForRecord()`.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function dataForForm(Model $record, array $data): array
    {
        return $data;
    }

    /**
     * And back: what the form sent, as the type expects it. `$record` is null
     * while creating.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function dataForRecord(?Model $record, array $data): array
    {
        return $data;
    }

    /**
     * Where a new record lands. The editor, because an empty draft is worth
     * nothing until it holds something — unless a type says otherwise.
     */
    public static function afterCreateUrl(Model $record): string
    {
        return static::builderUrl($record);
    }

    /**
     * Create a record the way its type does: a page decides its own slug, a mail
     * its untitled name, a document its default sheet. Only the attributes the
     * type accepts on creation reach it — it would drop the others anyway.
     *
     * @param  array<string, mixed>  $data
     */
    public static function createRecord(array $data): Model
    {
        $type = static::builderType();
        $data = static::dataForRecord(null, $data);

        return $type->create(Arr::only($data, array_keys($type->storeRules())));
    }

    /**
     * Apply the metadata of a record through its type, whose rules are also the
     * whitelist: what they don't mention is none of the type's business.
     *
     * @param  array<string, mixed>  $data
     */
    public static function updateRecord(Model $record, array $data): Model
    {
        $type = static::builderType();
        $data = static::dataForRecord($record, $data);

        return $type->update($record, Arr::only($data, array_keys($type->updateRules($record))));
    }

    /**
     * Opens the editor of a record: the core's own mount page, which stays on
     * with the management API off, and comes back here when it is closed.
     */
    public static function buildAction(): Action
    {
        return Action::make('build')
            ->label(__('Build'))
            ->icon('heroicon-o-pencil-square')
            ->url(static fn (Model $record): string => static::builderUrl($record));
    }

    public static function builderUrl(Model $record): string
    {
        return route('tagixo.builder.embed', [
            'type' => static::tagixoType(),
            'id' => $record->getKey(),
            'back' => static::getUrl('index'),
        ]);
    }

    /**
     * A short-lived URL showing the saved record as its visitors see it. Types
     * without a preview (a mail has no public page) get no action.
     */
    public static function previewAction(): ?Action
    {
        if (! static::builderType()->previewable()) {
            return null;
        }

        $ttl = max((int) config('tagixo.preview.url_ttl_seconds', 300), 1);

        return Action::make('preview')
            ->label(__('Preview'))
            ->icon('heroicon-o-eye')
            ->url(
                static fn (Model $record): ?string => static::builderType()->previewUrl($record, now()->addSeconds($ttl)),
                shouldOpenInNewTab: true,
            );
    }
}
