<?php

namespace Ccast\TagixoFilament\Filament\Resources\Sliders\Pages;

use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSlider extends EditRecord
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::forRoute('tagixo.sliders.edit'),

            DeleteAction::make(),
        ];
    }
}
