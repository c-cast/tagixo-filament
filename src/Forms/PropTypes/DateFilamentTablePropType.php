<?php

namespace Tagixo\Filament\Forms\PropTypes;

class DateFilamentTablePropType extends FilamentTablePropType
{
    protected function allowedColumnTypes(): array
    {
        return [
            ['value' => 'date', 'label' => __('Date')],
            ['value' => 'text', 'label' => __('Text')],
        ];
    }

    protected function defaultColumnType(): string
    {
        return 'date';
    }
}
