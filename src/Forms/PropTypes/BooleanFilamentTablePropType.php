<?php

namespace Tagixo\Filament\Forms\PropTypes;

class BooleanFilamentTablePropType extends FilamentTablePropType
{
    protected function allowedColumnTypes(): array
    {
        return [
            ['value' => 'boolean', 'label' => __('Boolean')],
        ];
    }

    protected function defaultColumnType(): string
    {
        return 'boolean';
    }
}
