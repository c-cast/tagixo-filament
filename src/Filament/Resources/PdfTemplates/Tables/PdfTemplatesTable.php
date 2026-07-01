<?php

namespace Ccast\TagixoFilament\Filament\Resources\PdfTemplates\Tables;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\TagixoFilament\Filament\Actions\PdfPreviewAction;
use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\PdfTemplateResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PdfTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->toggleable()
                    ->color('gray'),

                TextColumn::make('paper_size')
                    ->label(__('Paper size'))
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('orientation')
                    ->label(__('Orientation'))
                    ->formatStateUsing(fn ($state) => $state ? __(ucfirst((string) $state)) : null)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'archived' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(collect(PageStatus::cases())->mapWithKeys(
                        fn (PageStatus $status) => [$status->value => $status->label()]
                    )->all()),
            ])
            ->actions([
                VisualBuilderAction::make(PdfTemplateResource::class),

                PdfPreviewAction::make(),

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
