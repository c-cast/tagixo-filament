<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Tables;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\Tagixo\Models\Layout;
use Ccast\Tagixo\Models\Page;
use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Page $record): string => $record->url)
                    ->weight('medium'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (PageStatus $state): string => $state->color())
                    ->formatStateUsing(fn (PageStatus $state): string => $state->label()),

                TextColumn::make('layout_label')
                    ->label(__('Layout'))
                    ->state(function (Page $record): string {
                        if ($record->layout?->name) {
                            return $record->layout->name;
                        }

                        static $globalLayoutName = null;
                        static $globalLayoutResolved = false;

                        if (! $globalLayoutResolved) {
                            $globalLayoutName = Layout::global()?->name;
                            $globalLayoutResolved = true;
                        }

                        if ($globalLayoutName) {
                            return __('Global: :name', ['name' => $globalLayoutName]);
                        }

                        return __('No layout');
                    })
                    ->badge()
                    ->color(fn (Page $record): string => $record->layout_id ? 'primary' : 'gray')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label(__('Published'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('Not published'))
                    ->toggleable(),

                TextColumn::make('parent.title')
                    ->label(__('Parent'))
                    ->placeholder(__('Root'))
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'scheduled' => __('Scheduled'),
                        'archived' => __('Archived'),
                    ]),

                SelectFilter::make('layout_id')
                    ->label(__('Layout'))
                    ->relationship('layout', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),

                VisualBuilderAction::make(PageResource::class),

                Action::make('publish')
                    ->label(__('Publish'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Page $record) => $record->publish())
                    ->visible(fn (Page $record) => $record->status !== PageStatus::Published),

                Action::make('unpublish')
                    ->label(__('Unpublish'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Page $record) => $record->unpublish())
                    ->visible(fn (Page $record) => $record->status === PageStatus::Published),

                // Preview action - commented out as route doesn't exist in VB plugin
                // Action::make('preview')
                //     ->label(__('Preview'))
                //     ->icon('heroicon-o-eye')
                //     ->color('info')
                //     ->url(fn (Page $record) => route('tagixo.preview', $record))
                //     ->openUrlInNewTab(),

                Action::make('duplicate')
                    ->label(__('Duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (Page $record) {
                        $duplicate = $record->replicate();
                        $duplicate->title = $record->title.' '.__('(Copy)');
                        $duplicate->slug = $record->slug.'-copy-'.time();
                        $duplicate->status = PageStatus::Draft;
                        $duplicate->published_at = null;
                        $duplicate->save();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    Action::make('publishBulk')
                        ->label(__('Publish Selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->publish();
                            }
                        }),

                    Action::make('unpublishBulk')
                        ->label(__('Unpublish Selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->unpublish();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading(__('No pages yet'))
            ->emptyStateDescription(__('Create your first page to get started'))
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
