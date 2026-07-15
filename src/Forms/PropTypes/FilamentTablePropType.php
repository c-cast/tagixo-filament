<?php

namespace Ccast\TagixoFilament\Forms\PropTypes;

use Ccast\Tagixo\Core\PropTypes\AbstractPropType;
use Ccast\Tagixo\Core\Props\NumberProp;
use Ccast\Tagixo\Core\Props\RepeaterProp;
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

    protected function allowedColumnTypes(): array
    {
        return [
            ['value' => 'text',  'label' => __('Text')],
            ['value' => 'badge', 'label' => __('Badge')],
        ];
    }

    protected function defaultColumnType(): string
    {
        return 'text';
    }

    public function schema(): array
    {
        $themeColorOptions = [
            ['value' => 'success', 'label' => __('Success')],
            ['value' => 'warning', 'label' => __('Warning')],
            ['value' => 'danger',  'label' => __('Danger')],
            ['value' => 'info',    'label' => __('Info')],
            ['value' => 'primary', 'label' => __('Primary')],
            ['value' => 'gray',    'label' => __('Gray')],
        ];

        $sizeOptions = [
            ['value' => 'sm', 'label' => __('Small')],
            ['value' => 'md', 'label' => __('Medium')],
            ['value' => 'lg', 'label' => __('Large')],
            ['value' => 'xl', 'label' => __('Extra large')],
        ];

        return [
            // ── Base ────────────────────────────────────────────────────────
            ToggleProp::make('show_in_table')
                ->setLabel(__('Show as column'))
                ->default(false),

            TextProp::make('column_label')
                ->setLabel(__('Column label'))
                ->placeholder(__('Leave blank to use field label')),

            SelectProp::make('column_type')
                ->setLabel(__('Column type'))
                ->options($this->allowedColumnTypes())
                ->default($this->defaultColumnType()),

            // ── Common ──────────────────────────────────────────────────────
            ToggleProp::make('sortable')
                ->setLabel(__('Sortable'))
                ->default(false),

            ToggleProp::make('searchable')
                ->setLabel(__('Searchable'))
                ->default(false),

            ToggleProp::make('filterable')
                ->setLabel(__('Filterable'))
                ->default(false),

            ToggleProp::make('toggleable')
                ->setLabel(__('Toggleable (user can hide column)'))
                ->default(false),

            SelectProp::make('alignment')
                ->setLabel(__('Alignment'))
                ->options([
                    ['value' => 'left',   'label' => __('Left')],
                    ['value' => 'center', 'label' => __('Center')],
                    ['value' => 'right',  'label' => __('Right')],
                ])
                ->default('left'),

            TextProp::make('tooltip')
                ->setLabel(__('Tooltip')),

            // ── Inline editing ───────────────────────────────────────────────
            ToggleProp::make('editable_in_table')
                ->setLabel(__('Editable inline (in table)'))
                ->default(false),

            // ── Text column ─────────────────────────────────────────────────
            NumberProp::make('text_limit')
                ->setLabel(__('Truncate characters (0 = no limit)'))
                ->default(0)
                ->min(0)
                ->showWhen('column_type', 'text'),

            SelectProp::make('text_weight')
                ->setLabel(__('Font weight'))
                ->options([
                    ['value' => 'normal',   'label' => __('Normal')],
                    ['value' => 'semibold', 'label' => __('Semibold')],
                    ['value' => 'bold',     'label' => __('Bold')],
                ])
                ->default('normal')
                ->showWhen('column_type', 'text'),

            SelectProp::make('text_format')
                ->setLabel(__('Format'))
                ->options([
                    ['value' => 'none',    'label' => __('None')],
                    ['value' => 'numeric', 'label' => __('Numeric')],
                    ['value' => 'money',   'label' => __('Money (€)')],
                    ['value' => 'since',   'label' => __('Relative time ("3 days ago")')],
                ])
                ->default('none')
                ->showWhen('column_type', 'text'),

            NumberProp::make('text_decimals')
                ->setLabel(__('Decimal places'))
                ->default(2)
                ->min(0)
                ->showWhen('text_format', 'numeric'),

            TextProp::make('text_currency')
                ->setLabel(__('Currency'))
                ->placeholder(__('EUR'))
                ->showWhen('text_format', 'money'),

            ToggleProp::make('text_copyable')
                ->setLabel(__('Copyable'))
                ->default(false)
                ->showWhen('column_type', 'text'),

            TextProp::make('text_url')
                ->setLabel(__('URL (leave blank for plain text)'))
                ->placeholder(__('https://...'))
                ->showWhen('column_type', 'text'),

            ToggleProp::make('text_open_in_new_tab')
                ->setLabel(__('Open URL in new tab'))
                ->default(true)
                ->showWhen('column_type', 'text'),

            // ── Date column ─────────────────────────────────────────────────
            TextProp::make('date_format')
                ->setLabel(__('Date format'))
                ->placeholder(__('d/m/Y H:i'))
                ->showWhen('column_type', 'date'),

            ToggleProp::make('date_since')
                ->setLabel(__('Show as relative time ("3 days ago")'))
                ->default(false)
                ->showWhen('column_type', 'date'),

            // ── Boolean / Icon column ────────────────────────────────────────
            TextProp::make('bool_true_icon')
                ->setLabel(__('Icon when true'))
                ->placeholder(__('heroicon-o-check-circle'))
                ->showWhen('column_type', 'boolean'),

            TextProp::make('bool_false_icon')
                ->setLabel(__('Icon when false'))
                ->placeholder(__('heroicon-o-x-circle'))
                ->showWhen('column_type', 'boolean'),

            SelectProp::make('bool_true_color')
                ->setLabel(__('Color when true'))
                ->options($themeColorOptions)
                ->default('success')
                ->showWhen('column_type', 'boolean'),

            SelectProp::make('bool_false_color')
                ->setLabel(__('Color when false'))
                ->options($themeColorOptions)
                ->default('danger')
                ->showWhen('column_type', 'boolean'),

            SelectProp::make('bool_size')
                ->setLabel(__('Icon size'))
                ->options($sizeOptions)
                ->default('md')
                ->showWhen('column_type', 'boolean'),

            // ── Badge column ─────────────────────────────────────────────────
            RepeaterProp::make('badge_colors')
                ->setLabel(__('Value → color'))
                ->itemLabel(__('Color rule'))
                ->fields([
                    TextProp::make('value')->setLabel(__('Value')),
                    SelectProp::make('color')
                        ->setLabel(__('Color'))
                        ->options($themeColorOptions)
                        ->default('primary'),
                ])
                ->showWhen('column_type', 'badge'),

            RepeaterProp::make('badge_icons')
                ->setLabel(__('Value → icon'))
                ->itemLabel(__('Icon rule'))
                ->fields([
                    TextProp::make('value')->setLabel(__('Value')),
                    TextProp::make('icon')
                        ->setLabel(__('Icon'))
                        ->placeholder(__('heroicon-o-check')),
                ])
                ->showWhen('column_type', 'badge'),

            // ── Image column ─────────────────────────────────────────────────
            SelectProp::make('image_shape')
                ->setLabel(__('Shape'))
                ->options([
                    ['value' => 'square',   'label' => __('Square')],
                    ['value' => 'rounded',  'label' => __('Rounded')],
                    ['value' => 'circular', 'label' => __('Circular')],
                ])
                ->default('square')
                ->showWhen('column_type', 'image'),

            TextProp::make('image_height')
                ->setLabel(__('Height'))
                ->placeholder(__('40px'))
                ->showWhen('column_type', 'image'),

            SelectProp::make('image_size')
                ->setLabel(__('Size'))
                ->options($sizeOptions)
                ->default('md')
                ->showWhen('column_type', 'image'),
        ];
    }
}
