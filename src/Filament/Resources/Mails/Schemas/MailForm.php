<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MailForm
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
                    ->options([
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'scheduled' => __('Scheduled'),
                        'archived' => __('Archived'),
                    ])
                    ->default('draft')
                    ->required()
                    ->suffixIcon('heroicon-o-envelope'),

                TextInput::make('subject')
                    ->label(__('Subject'))
                    ->maxLength(255)
                    ->helperText(__('Default subject used when sending this template. Can be overridden at send time.'))
                    ->columnSpanFull(),

                Textarea::make('preheader')
                    ->label(__('Preheader'))
                    ->rows(2)
                    ->maxLength(255)
                    ->helperText(__('Short summary shown after the subject in most inbox clients.'))
                    ->columnSpanFull(),

                DateTimePicker::make('published_at')
                    ->label(__('Publish Date'))
                    ->suffixIcon('heroicon-o-calendar'),
            ]);
    }
}
