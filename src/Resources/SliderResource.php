<?php

namespace Tagixo\Filament\Resources;

/**
 * Sliders of the slider builder.
 */
class SliderResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'sliders';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
