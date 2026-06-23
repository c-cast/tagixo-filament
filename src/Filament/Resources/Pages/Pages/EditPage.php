<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Pages;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
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
            Action::make('openVisualBuilder')
                ->label(__('Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->badge(__('New'))
                ->badgeColor('success')
                ->url(fn ($record) => PageResource::getUrl('build', ['record' => $record]))
                ->tooltip(__('Switch to visual builder mode')),

            Action::make('editLayoutHeader')
                ->label(__('Layout Header'))
                ->icon('heroicon-o-squares-2x2')
                ->color('gray')
                ->url(fn () => $this->getLayoutBuildUrl('header'))
                ->visible(fn () => $this->resolveEffectiveLayoutId() !== null)
                ->tooltip(__('Edit header of the assigned/global layout')),

            Action::make('editLayoutFooter')
                ->label(__('Layout Footer'))
                ->icon('heroicon-o-paint-brush')
                ->color('gray')
                ->url(fn () => $this->getLayoutBuildUrl('footer'))
                ->visible(fn () => $this->resolveEffectiveLayoutId() !== null)
                ->tooltip(__('Edit footer of the assigned/global layout')),

            Action::make('manageLayouts')
                ->label(__('Manage Layouts'))
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('gray')
                ->url(fn () => LayoutResource::getUrl('index'))
                ->tooltip(__('Open layouts management')),

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

    protected function resolveEffectiveLayoutId(): ?int
    {
        $layout = $this->getRecord()->layout ?? $this->getRecord()->getEffectiveLayout();

        return $layout?->id;
    }

    protected function getLayoutBuildUrl(string $section): string
    {
        $layoutId = $this->resolveEffectiveLayoutId();

        if ($layoutId === null) {
            return LayoutResource::getUrl('index');
        }

        return LayoutResource::getUrl('build', [
            'record' => $layoutId,
            'section' => $section,
        ]);
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
