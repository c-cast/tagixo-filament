<?php

namespace Ccast\TagixoFilament\Filament\Resources\Sliders\Pages;

use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSliders extends ListRecords
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createSlider')
                ->label(__('Create new slider'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn (): string => route('tagixo.sliders.new'))
                ->openUrlInNewTab()
                ->tooltip(__('Opens the visual slider builder in a new tab and creates a draft')),
        ];
    }
}
