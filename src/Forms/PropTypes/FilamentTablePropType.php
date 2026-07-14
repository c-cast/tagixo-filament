<?php

namespace Ccast\TagixoFilament\Forms\PropTypes;

use Ccast\Tagixo\Core\PropTypes\AbstractPropType;
use Ccast\Tagixo\Core\Props\SelectProp;
use Ccast\Tagixo\Core\Props\TextProp;
use Ccast\Tagixo\Core\Props\ToggleProp;

class FilamentTablePropType extends AbstractPropType
{
    public function key(): string
    {
        return 'table';
    }

    public function label(): string
    {
        return __('Table');
    }

    public function tab(): string
    {
        return 'table';
    }

    public function schema(): array
    {
        return [
            ToggleProp::make('show_in_table')
                ->setLabel(__('Show as column'))
                ->default(false),

            TextProp::make('column_label')
                ->setLabel(__('Column label'))
                ->placeholder(__('Leave blank to use field label')),

            SelectProp::make('column_type')
                ->setLabel(__('Column type'))
                ->options([
                    'text'    => __('Text'),
                    'badge'   => __('Badge'),
                    'date'    => __('Date'),
                    'boolean' => __('Boolean'),
                ])
                ->default('text'),

            ToggleProp::make('sortable')
                ->setLabel(__('Sortable'))
                ->default(false),

            ToggleProp::make('searchable')
                ->setLabel(__('Searchable'))
                ->default(false),
        ];
    }
}
