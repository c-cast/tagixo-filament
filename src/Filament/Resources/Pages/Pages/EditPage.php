<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Pages;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(PageResource::class),

            // Header/footer are edited from the Layout resource (Layouts →
            // Edit Header / Edit Footer), not from the page.

            // Preview action - commented out as route doesn't exist in VB plugin
            // Action::make('preview')
            //     ->label(__('Preview'))
            //     ->icon('heroicon-o-eye')
            //     ->color('info')
            //     ->url(fn ($record) => route('tagixo.preview', ['page' => $record]))
            //     ->openUrlInNewTab(),

            Action::make('publish')
                ->label(__('Publish'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->getRecord()->publish())
                ->visible(fn () => $this->getRecord()->status !== PageStatus::Published),

            Action::make('unpublish')
                ->label(__('Unpublish'))
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->action(fn () => $this->getRecord()->unpublish())
                ->visible(fn () => $this->getRecord()->status === PageStatus::Published),

            // Cache invalidation action removed for now (not needed in VB)
            // Action::make('invalidateCache')
            //     ->label(__('Clear Cache'))
            //     ->icon('heroicon-o-arrow-path')
            //     ->color('gray')
            //     ->requiresConfirmation()
            //     ->action(fn () => $this->getRecord()->invalidateCache()),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Invalidate cache when content changes
        if ($this->getRecord()->wasChanged('content')) {
            $data['rendered_html'] = null;
            $data['css'] = null;
        }

        return $data;
    }
}
