<?php

namespace Ccast\TagixoFilament\Forms\PropTypes;

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
