<?php

namespace Tagixo\Filament\Resources;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Tagixo\Core\Facades\Tagixo;
use Tagixo\PageBuilder\Models\Page;
use Tagixo\PageBuilder\Services\LayoutConditionService;
use Tagixo\PageBuilder\Services\LayoutSections;

/**
 * Layouts of the page builder — the header and footer pages wear — which the
 * `layouts` builder type makes a record like any other. What this resource adds
 * is the thing only a layout has: the conditions that decide which pages wear it,
 * and one Build action per section, because a layout holds two documents.
 *
 * Its body is not here: pages own their body, and a template scoped to a model
 * stands for that model's archive or single page (the Theme Builder shows all
 * three).
 *
 * The Theme Builder is the way in, so this resource stays out of the navigation:
 * a template and its layout are the same thing, seen from two screens.
 */
class LayoutResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'layouts';

    /** The Theme Builder is the way in. */
    protected static bool $shouldRegisterNavigation = false;

    /**
     * A layout and a template are the same thing seen from two screens, and in a
     * panel the word is template: that is what the Theme Builder calls them.
     */
    public static function getNavigationLabel(): string
    {
        return __('Templates');
    }

    public static function getModelLabel(): string
    {
        return __('Template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Templates');
    }

    public static function table(Table $table): Table
    {
        $table = parent::table($table);

        return $table
            ->columns([
                ...$table->getColumns(),
                TextColumn::make('conditions')
                    ->label(__('Applies to'))
                    ->formatStateUsing(static fn (mixed $state): string => static::conditionsSummary($state)),
            ])
            ->recordActions([
                ...static::sectionActions(),
                ...array_values(array_filter(
                    $table->getRecordActions(),
                    // The generic Build opens one scope: a layout has two.
                    static fn ($action): bool => $action->getName() !== 'build',
                )),
            ]);
    }

    /**
     * One action per document a layout holds.
     *
     * @return list<Action>
     */
    public static function sectionActions(): array
    {
        return array_map(
            static fn (string $section): Action => Action::make($section)
                ->label($section === 'header' ? __('Header') : __('Footer'))
                ->icon($section === 'header' ? 'heroicon-o-arrow-up' : 'heroicon-o-arrow-down')
                ->url(static fn (Model $record): string => static::builderUrl($record, $section)),
            LayoutSections::SECTIONS,
        );
    }

    /**
     * The editor of one section. Without a section it is the header, which is
     * what `LayoutType` opens by default.
     */
    public static function builderUrl(Model $record, ?string $section = null): string
    {
        return route('tagixo.builder.embed', [
            'type' => static::tagixoType(),
            'id' => $record->getKey(),
            'back' => static::getUrl('index'),
            ...($section !== null ? ['scope' => $section] : []),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        return $schema->components([
            ...$schema->getComponents(),
            ...static::conditionFields(),
        ]);
    }

    /**
     * What a template claims, as five plain questions instead of a repeater of
     * {type, target} rows: every page, the homepage, these pages, everything of
     * these content types, the archive of these content types.
     *
     * Flat on purpose: a repeater would have to show the page picker only for one
     * kind of condition and the content-type picker only for two others, and a
     * screen that shows every picker on every row is unreadable.
     *
     * @return list<Field>
     */
    public static function conditionFields(): array
    {
        return [
            Toggle::make('condition_all_pages')
                ->label(__('Every page'))
                ->helperText(__('Unless a template claims one of them more precisely.')),
            Toggle::make('condition_homepage')
                ->label(__('The homepage')),
            // A question nobody has answered yet is answered with "none": the
            // empty list, not null, which a multiple select refuses.
            Select::make('condition_pages')
                ->label(__('Specific pages'))
                ->multiple()
                ->default([])
                ->options(static fn (): array => static::pageOptions()),
            Select::make('condition_models')
                ->label(__('Everything of these content types'))
                ->multiple()
                ->default([])
                ->options(static fn (): array => static::modelOptions()),
            Select::make('condition_model_archives')
                ->label(__('Archive of these content types'))
                ->multiple()
                ->default([])
                ->options(static fn (): array => static::modelOptions()),
        ];
    }

    /**
     * A new template lands on its own screen and not in the editor: a header
     * nobody asked for yet is not where you want to be. It is created already
     * knowing what it claims, so there is nothing left to come back for.
     */
    public static function afterCreateUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record->getKey()]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function dataForForm(Model $record, array $data): array
    {
        return [...$data, ...static::conditionsToForm($record->conditions)];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function dataForRecord(?Model $record, array $data): array
    {
        $data['conditions'] = static::conditionsFromForm($data, $record?->conditions ?? []);

        return $data;
    }

    /**
     * The stored conditions as those five answers.
     *
     * @return array<string, mixed>
     */
    public static function conditionsToForm(mixed $conditions): array
    {
        $conditions = is_array($conditions) ? $conditions : [];
        $answers = [
            'condition_all_pages' => false,
            'condition_homepage' => false,
            'condition_pages' => [],
            'condition_models' => [],
            'condition_model_archives' => [],
        ];

        foreach ($conditions as $condition) {
            if (! is_array($condition)) {
                continue;
            }

            match ($condition['type'] ?? null) {
                'all_pages' => $answers['condition_all_pages'] = true,
                'homepage' => $answers['condition_homepage'] = true,
                'page_id' => $answers['condition_pages'][] = $condition['value'] ?? null,
                'model_all' => $answers['condition_models'][] = $condition['model'] ?? null,
                'model_archive' => $answers['condition_model_archives'][] = $condition['model'] ?? null,
                default => null,
            };
        }

        foreach (['condition_pages', 'condition_models', 'condition_model_archives'] as $key) {
            $answers[$key] = array_values(array_filter($answers[$key], static fn (mixed $value): bool => $value !== null));
        }

        return $answers;
    }

    /**
     * And back. Conditions of a kind this screen does not ask about — a single
     * record, a taxonomy term, the legacy template types — are assigned
     * elsewhere and are carried over untouched: a form must not delete what it
     * cannot see.
     *
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    public static function conditionsFromForm(array $data, mixed $existing = []): array
    {
        $kept = array_values(array_filter(
            is_array($existing) ? $existing : [],
            static fn (mixed $condition): bool => is_array($condition)
                && ! in_array($condition['type'] ?? null, ['all_pages', 'homepage', 'page_id', 'model_all', 'model_archive'], true),
        ));

        $conditions = [];

        if (! empty($data['condition_all_pages'])) {
            $conditions[] = ['type' => 'all_pages'];
        }

        if (! empty($data['condition_homepage'])) {
            $conditions[] = ['type' => 'homepage'];
        }

        foreach (static::values($data['condition_pages'] ?? []) as $page) {
            $conditions[] = ['type' => 'page_id', 'value' => $page];
        }

        foreach (static::values($data['condition_models'] ?? []) as $model) {
            $conditions[] = ['type' => 'model_all', 'model' => $model];
        }

        foreach (static::values($data['condition_model_archives'] ?? []) as $model) {
            $conditions[] = ['type' => 'model_archive', 'model' => $model];
        }

        return [...$conditions, ...$kept];
    }

    /**
     * @return list<mixed>
     */
    protected static function values(mixed $value): array
    {
        return array_values(array_filter(
            is_array($value) ? $value : [],
            static fn (mixed $entry): bool => $entry !== null && $entry !== '',
        ));
    }

    /**
     * @return array<int|string, string>
     */
    public static function pageOptions(): array
    {
        return config('tagixo-page-builder.models.page', Page::class)::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function modelOptions(): array
    {
        $options = [];

        foreach (Tagixo::getRegisteredModels() as $key => $model) {
            $options[$key] = (string) ($model['label'] ?? $key);
        }

        return $options;
    }

    /**
     * What a listing shows instead of raw conditions: the labels the page builder
     * itself writes for them.
     */
    public static function conditionsSummary(mixed $conditions): string
    {
        if (! is_array($conditions) || $conditions === []) {
            return __('Nothing yet');
        }

        $service = app(LayoutConditionService::class);

        return implode(', ', array_map(
            static fn (mixed $condition): string => is_array($condition) ? $service->getConditionLabel($condition) : '',
            $conditions,
        ));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLayouts::route('/'),
            'create' => Pages\CreateLayout::route('/create'),
            'edit' => Pages\EditLayout::route('/{record}/edit'),
        ];
    }
}
