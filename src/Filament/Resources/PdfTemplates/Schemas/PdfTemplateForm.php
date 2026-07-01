<?php

namespace Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Schemas;

use Ccast\Tagixo\Enums\PageStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PdfTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn ($state, callable $set, $get) => $get('slug') ?: $set('slug', Str::slug((string) $state))
                    )
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->suffixIcon('heroicon-o-link')
                    ->placeholder(__('Slug')),

                Select::make('status')
                    ->label(__('Status'))
                    ->options(collect(PageStatus::cases())->mapWithKeys(
                        fn (PageStatus $status) => [$status->value => $status->label()]
                    )->all())
                    ->default(PageStatus::Draft->value)
                    ->required()
                    ->suffixIcon('heroicon-o-document-text'),

                Select::make('paper_size')
                    ->label(__('Paper size'))
                    ->options([
                        'A4' => 'A4',
                        'A5' => 'A5',
                        'A3' => 'A3',
                        'Letter' => __('Letter'),
                        'Legal' => __('Legal'),
                    ])
                    ->default('A4')
                    ->required()
                    ->native(false),

                Select::make('orientation')
                    ->label(__('Orientation'))
                    ->options([
                        'portrait' => __('Portrait'),
                        'landscape' => __('Landscape'),
                    ])
                    ->default('portrait')
                    ->required()
                    ->native(false),

                TextInput::make('margin')
                    ->label(__('Margin'))
                    ->maxLength(50)
                    ->default('2cm')
                    ->placeholder('2cm')
                    ->helperText(__('CSS @page margin, e.g. "2cm" or "10mm 15mm".')),

                DateTimePicker::make('published_at')
                    ->label(__('Publish Date'))
                    ->suffixIcon('heroicon-o-calendar'),
            ]);
    }
}
