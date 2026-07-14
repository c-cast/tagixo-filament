<?php

namespace Ccast\TagixoFilament\Filament\Resources\Forms\Tables;

use Ccast\Tagixo\Tagixo;
use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        $locked = app(Tagixo::class)->getLockedFormTarget();

        $columns = [
            TextColumn::make('title')
                ->label(__('Title'))
                ->searchable()
                ->sortable()
                ->weight('medium'),

            TextColumn::make('slug')
                ->label(__('Slug'))
                ->searchable()
                ->toggleable()
                ->color('gray'),

            TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'published' => 'success',
                    'draft' => 'warning',
                    'archived' => 'gray',
                    default => 'gray',
                }),

            TextColumn::make('updated_at')
                ->label(__('Updated'))
                ->dateTime()
                ->sortable()
                ->since(),
        ];

        // Show form_target badge only when the panel is not locked to a single target
        if ($locked === null) {
            $columns[] = TextColumn::make('form_target')
                ->label(__('Target'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'app' => 'primary',
                    default => 'gray',
                })
                ->toggleable();
        }

        $filters = [
            SelectFilter::make('status')
                ->label(__('Status'))
                ->options([
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'archived' => __('Archived'),
                ]),
        ];

        // Show form_target filter only when the panel is not locked
        if ($locked === null) {
            $filters[] = SelectFilter::make('form_target')
                ->label(__('Target'))
                ->options([
                    'universal' => __('Universal'),
                    'app' => __('App only'),
                ]);
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions([
                VisualBuilderAction::forRoute('builder.forms.edit'),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
