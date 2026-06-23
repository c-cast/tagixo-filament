<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Schemas;

use Ccast\TagixoFilament\Filament\Resources\Menus\Forms\MenuTreeField;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Menu details'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn ($state, callable $set, $get) => $get('slug') ?: $set('slug', Str::slug((string) $state))
                        ),

                    TextInput::make('slug')
                        ->label(__('Slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->suffixIcon('heroicon-o-link')
                        ->placeholder(__('e.g. main-nav'))
                        ->helperText(__('Unique identifier used to reference this menu from modules.')),

                    Textarea::make('description')
                        ->label(__('Description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    TextInput::make('css_class')
                        ->label(__('Wrapper CSS class'))
                        ->placeholder(__('e.g. navbar-primary'))
                        ->helperText(__('Applied to the <nav> wrapper when the menu is rendered.')),
                ])
                ->columns(2),

            Section::make(__('Items'))
                ->schema([
                    MenuTreeField::make('items')
                        ->label(__('Menu items'))
                        ->helperText(__('Drag the handle to reorder, use the arrows to change level, and the pencil to edit an item. Supports unlimited nesting.')),
                ]),
        ]);
    }
}
