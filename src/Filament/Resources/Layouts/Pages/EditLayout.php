<?php

namespace Ccast\TagixoFilament\Filament\Resources\Layouts\Pages;

use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditLayout extends EditRecord
{
    protected static string $resource = LayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('build_header')
                ->label(__('Edit Header'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => LayoutResource::getUrl('build', [
                    'record' => $this->record,
                    'section' => 'header',
                ])),
            Action::make('build_footer')
                ->label(__('Edit Footer'))
                ->icon('heroicon-o-paint-brush')
                ->color('gray')
                ->url(fn () => LayoutResource::getUrl('build', [
                    'record' => $this->record,
                    'section' => 'footer',
                ])),
        ];
    }
}
