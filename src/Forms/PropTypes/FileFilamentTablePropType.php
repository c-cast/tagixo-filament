<?php

namespace Ccast\TagixoFilament\Forms\PropTypes;

class FileFilamentTablePropType extends FilamentTablePropType
{
    protected function allowedColumnTypes(): array
    {
        return [
            ['value' => 'image', 'label' => __('Image')],
            ['value' => 'text',  'label' => __('Text')],
        ];
    }

    protected function defaultColumnType(): string
    {
        return 'image';
    }
}
