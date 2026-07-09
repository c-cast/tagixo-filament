<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Pages;

use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    /**
     * "Pages" lists the user-managed pages; "Model templates" the archive /
     * single pages kept in sync from the registered model routes
     * (tagixo:sync-pages) — reachable here so their content can be edited in
     * the visual builder, while creation/deletion stays with the sync.
     */
    public function getTabs(): array
    {
        return [
            'pages' => \Filament\Schemas\Components\Tabs\Tab::make(__('Pages'))
                ->modifyQueryUsing(fn ($query) => $query->userManaged()),
            'templates' => \Filament\Schemas\Components\Tabs\Tab::make(__('Model templates'))
                ->modifyQueryUsing(fn ($query) => $query->whereNotNull('source')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('generateSitemap')
                ->label(__('Generate Sitemap'))
                ->icon('heroicon-o-map')
                ->color('gray')
                // A single global sitemap makes no sense in some setups (e.g. a
                // per-tenant demo). Toggle off via config to hide the action.
                ->visible(fn (): bool => (bool) config('tagixo-filament.pages.sitemap_action', true))
                ->requiresConfirmation()
                ->modalHeading(__('Generate Sitemap'))
                ->modalDescription(__('This will regenerate public/sitemap.xml with all currently published pages.'))
                ->modalSubmitActionLabel(__('Generate'))
                ->action(function () {
                    try {
                        Artisan::call('tagixo:generate-sitemap');

                        Notification::make()
                            ->title(__('Sitemap generated'))
                            ->body(__('public/sitemap.xml has been updated.'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title(__('Sitemap generation failed'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
