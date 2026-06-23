<?php

namespace Ccast\TagixoFilament\Filament\Resources\Sliders\Pages;

use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSlider extends EditRecord
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn (): string => route('builder.sliders.edit', $this->record->id))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
