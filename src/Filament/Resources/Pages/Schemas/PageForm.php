<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Schemas;

use Ccast\Tagixo\Models\Layout;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Header Zone
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn ($state, callable $set, $get) => $get('slug') ?: $set('slug', Str::slug($state))
                    )
                    ->columnSpanFull(),

                // Important Info Zone
                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->hiddenLabel()
                    ->suffixIcon('heroicon-o-link')
                    ->placeholder(__('Slug')),

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
                    ->hiddenLabel()
                    ->suffixIcon('heroicon-o-document-text')
                    ->placeholder(__('Status')),

                DateTimePicker::make('published_at')
                    ->label(__('Publish Date'))
                    ->hiddenLabel()
                    ->suffixIcon('heroicon-o-calendar')
                    ->placeholder(__('Publish Date')),

                // HR Separator
                Html::make("<hr class='my-6 text-gray-300 w-full' />"),

                // Body Zone 7/5
                Group::make()
                    ->schema([
                        Select::make('template')
                            ->label(__('Template'))
                            ->options([
                                'default' => __('Default'),
                                'landing' => __('Landing Page'),
                                'contact' => __('Contact'),
                                'about' => __('About'),
                                'product' => __('Product'),
                            ])
                            ->default('default')
                            ->required()
                            ->inlineLabel(),

                        Select::make('theme')
                            ->label(__('Theme'))
                            ->options([
                                'default' => __('Default Theme'),
                                'dark' => __('Dark Theme'),
                                'minimal' => __('Minimal Theme'),
                            ])
                            ->nullable()
                            ->inlineLabel(),

                        Select::make('parent_id')
                            ->label(__('Parent Page'))
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->nullable()
                            ->inlineLabel(),

                        Select::make('layout_id')
                            ->label(__('Layout'))
                            ->options(fn () => Layout::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(__('If empty, the global layout will be used.'))
                            ->inlineLabel(),
                    ])
                    ->columnSpan(7),

                Group::make()
                    ->schema([
                        Textarea::make('excerpt')
                            ->label(__('Excerpt'))
                            ->rows(3)
                            ->maxLength(500)
                            ->inlineLabel(),

                        TextInput::make('meta_title')
                            ->label(__('Meta Title'))
                            ->maxLength(60)
                            ->inlineLabel(),

                        Textarea::make('meta_description')
                            ->label(__('Meta Description'))
                            ->rows(3)
                            ->maxLength(160)
                            ->inlineLabel(),
                    ])
                    ->columnSpan(5),

                // Notes/Description Zone
                FileUpload::make('og_image')
                    ->label(__('OpenGraph Image'))
                    ->image()
                    ->maxSize(2048)
                    ->columnSpanFull()
                    ->inlineLabel(),
            ]);
    }
}
