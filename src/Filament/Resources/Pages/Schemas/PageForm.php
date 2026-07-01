<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Schemas;

use Ccast\Tagixo\Facades\Tagixo;
use Ccast\Tagixo\Models\Layout;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(12)->components([
            Group::make([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, callable $set, $get) => $get('slug') ?: $set('slug', Str::slug($state))
                            )->columnSpan([
                                'default' => 12,
                                '2xl' => 7
                            ]),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->suffixIcon('heroicon-o-link')
                            ->placeholder(__('Slug'))
                            ->helperText(fn (): string => __('Use the slug ":slug" to make this page the site homepage (served at /).', [
                                'slug' => config('tagixo.frontend.home_slug', 'home'),
                            ]))
                            ->columnSpan([
                                'default' => 12,
                                '2xl' => 5
                            ]),
                        Select::make('parent_id')
                            ->label(__('Parent Page'))
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->nullable()
                            ->columnSpan([
                                'default' => 12,
                                '2xl' => 6
                            ]),

                        Select::make('layout_id')
                            ->label(__('Layout'))
                            ->options(fn () => Layout::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => Layout::global()?->id)
                            ->helperText(__('Other layouts override the header/footer; empty sections fall back to the Global Layout.'))
                            ->columnSpan([
                                'default' => 12,
                                '2xl' => 6
                            ]),
                        Textarea::make('excerpt')
                            ->label(__('Excerpt'))
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull()
                    ])
                    ->columnSpanFull()
                    ->columns(12),
                Section::make(__("Template"))
                ->schema([
                    Select::make('template_type')
                        ->label(__('Template Type'))
                        ->options([
                            'static' => __('Static'),
                            'archive' => __('Archive (all records)'),
                            'single' => __('Single (one record per URL)'),
                            'specific' => __('Specific (fixed record)'),
                        ])
                        ->default('static')
                        ->live()
                        ->helperText(__('Static: normal page. Archive: lists all model records. Single: /slug/{record}. Specific: always shows the same record.'))
                        ->columnSpanFull(),

                    Select::make('model_class')
                        ->label(__('Model'))
                        ->options(fn () => collect(Tagixo::getRegisteredModels())->mapWithKeys(fn ($m) => [$m['class'] => $m['label']])->all())
                        ->searchable()
                        ->nullable()
                        ->helperText(__('The Eloquent model that provides data for this template page.'))
                        ->visible(fn ($get) => in_array($get('template_type'), ['archive', 'single', 'specific'], true))
                        ->columnSpan([
                            'default' => 12,
                            '2xl' => 8
                        ]),

                    Select::make('model_url_key')
                        ->label(__('URL Key'))
                        ->options(fn ($get) => self::resolveModelAttributes($get('model_class')))
                        ->default('id')
                        ->helperText(__('The model attribute used as the URL parameter (e.g. id, slug).'))
                        ->visible(fn ($get) => $get('template_type') === 'single')
                        ->columnSpan([
                            'default' => 12,
                            '2xl' => 4
                        ]),

                    TextInput::make('model_id')
                        ->label(__('Record ID'))
                        ->numeric()
                        ->nullable()
                        ->helperText(__('The ID of the specific record to display on this page.'))
                        ->visible(fn ($get) => $get('template_type') === 'specific')
                        ->columnSpan([
                            'default' => 12,
                            '2xl' => 4
                        ]),
                ])->columnSpanFull()->columns(12)
            ])->columnSpan([
                'default' => 12,
                '2xl' => 8
            ]),

            Group::make([
                Section::make()
                ->schema([
                    Select::make('status')
                        ->label(__('Status'))
                        ->options([
                            'draft' => __('Draft'),
                            'published' => __('Published'),
                            'scheduled' => __('Scheduled'),
                            'archived' => __('Archived'),
                        ])
                        ->default('draft')
                        ->required()
                        ->columnSpanFull(),

                    DateTimePicker::make('published_at')
                        ->label(__('Publish Date'))
                        ->helperText(__('Leave empty to publish immediately. Otherwise the page goes live from this date.'))
                        ->columnSpanFull(),

                    DateTimePicker::make('published_until')
                        ->label(__('Publish Until'))
                        ->helperText(__('Leave empty to keep it published indefinitely. Otherwise the page is hidden after this date.'))
                        ->columnSpanFull(),
                ])->columnSpanFull(),
            ])->columnSpan([
                'default' => 12,
                '2xl' => 4
            ]),
            Section::make(__("Meta"))
                ->schema([
                    TextInput::make('meta_title')
                        ->label(__('Meta Title'))
                        ->maxLength(60),

                    Textarea::make('meta_description')
                        ->label(__('Meta Description'))
                        ->rows(3)
                        ->maxLength(160),

                    FileUpload::make('og_image')
                        ->label(__('OpenGraph Image'))
                        ->image()
                        ->maxSize(2048),
                ])
                ->columnSpanFull(),



        ]);
    }

    private static function resolveModelAttributes(?string $modelClass): array
    {
        if (! $modelClass || ! class_exists($modelClass)) {
            return ['id' => 'id'];
        }

        try {
            $instance = new $modelClass;
            $columns = $instance->getFillable();
            $result = ['id' => 'id'];
            foreach ($columns as $col) {
                $result[$col] = $col;
            }

            return $result;
        } catch (\Throwable) {
            return ['id' => 'id'];
        }
    }
}
