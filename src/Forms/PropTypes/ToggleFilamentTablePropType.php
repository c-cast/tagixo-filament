<?php

namespace Ccast\TagixoFilament\Forms\PropTypes;

class ToggleFilamentTablePropType extends FilamentTablePropType
{
    protected function allowedColumnTypes(): array
    {
        return [
            ['value' => 'toggle', 'label' => __('Toggle')],
            ['value' => 'boolean', 'label' => __('Boolean')],
        ];
    }

    protected function defaultColumnType(): string
    {
        return 'toggle';
    }
}
