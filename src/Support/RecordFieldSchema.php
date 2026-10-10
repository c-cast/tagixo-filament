<?php

namespace Tagixo\Filament\Support;

use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Tagixo\Core\Builder\Types\RecordField;
use Tagixo\Core\Enums\RecordFieldType;

/**
 * Turns the fields a Tagixo record type declares into Filament columns and form
 * inputs. This is the whole reason the SDK does not need one hand-written
 * resource per builder: the type says what its records are made of, this class
 * says what that looks like in a panel.
 */
class RecordFieldSchema
{
    /**
     * Table columns of the fields the type lists.
     *
     * @param  list<RecordField>  $fields
     * @return list<Column>
     */
    public static function columns(array $fields): array
    {
        return array_values(array_map(
            static fn (RecordField $field): Column => static::column($field),
            array_filter($fields, static fn (RecordField $field): bool => $field->listed),
        ));
    }

    /**
     * Form inputs of the fields the type lets an editor change.
     *
     * `$createAttributes` are the attributes the type accepts when creating a
     * record (the keys of its storeRules): anything else is hidden on the create
     * screen, because `create()` would drop it — a page decides its own slug.
     *
     * @param  list<RecordField>  $fields
     * @param  list<string>|null  $createAttributes
     * @return list<Field>
     */
    public static function inputs(array $fields, ?array $createAttributes = null): array
    {
        $inputs = [];

        foreach ($fields as $field) {
            if (! $field->editable) {
                continue;
            }

            $input = static::input($field);

            if ($createAttributes !== null && ! in_array($field->name, $createAttributes, true)) {
                $input->hiddenOn('create');
            }

            $inputs[] = $input;
        }

        return $inputs;
    }

    public static function column(RecordField $field): Column
    {
        $column = match ($field->type) {
            RecordFieldType::Toggle => IconColumn::make($field->name)->boolean(),
            RecordFieldType::DateTime => TextColumn::make($field->name)->dateTime(),
            RecordFieldType::Textarea => TextColumn::make($field->name)->limit(60),
            RecordFieldType::Status => static::badgeColumn($field),
            RecordFieldType::Select => TextColumn::make($field->name)
                ->formatStateUsing(static fn (mixed $state): ?string => static::optionLabel($field, $state)),
            default => TextColumn::make($field->name),
        };

        $column->label($field->resolvedLabel());

        if ($field->sortable) {
            $column->sortable();
        }

        if ($field->searchable && $column instanceof TextColumn) {
            $column->searchable();
        }

        return $column;
    }

    public static function input(RecordField $field): Field
    {
        $input = match ($field->type) {
            RecordFieldType::Textarea => Textarea::make($field->name),
            RecordFieldType::Toggle => Toggle::make($field->name),
            RecordFieldType::DateTime => DateTimePicker::make($field->name),
            RecordFieldType::Number => TextInput::make($field->name)->integer(),
            RecordFieldType::Status, RecordFieldType::Select => Select::make($field->name)
                ->options(static::options($field)),
            default => TextInput::make($field->name),
        };

        $input->label($field->resolvedLabel());

        if ($field->required) {
            $input->required();
        }

        if ($field->help !== null) {
            $input->helperText($field->help);
        }

        if ($field->max !== null && ($input instanceof TextInput || $input instanceof Textarea)) {
            $input->maxLength($field->max);
        }

        return $input;
    }

    /**
     * The attribute a listing shows as the record's own name: the first field
     * the type declares, which the base type makes the title column.
     *
     * @param  list<RecordField>  $fields
     */
    public static function titleAttribute(array $fields): ?string
    {
        return $fields[0]->name ?? null;
    }

    /**
     * Value of an attribute as the panel needs it: an enum cast (a status) is
     * its own value, which is what the declared options are keyed by.
     */
    public static function state(Model $record, string $attribute): mixed
    {
        $value = $record->getAttribute($attribute);

        return $value instanceof BackedEnum ? $value->value : $value;
    }

    protected static function badgeColumn(RecordField $field): TextColumn
    {
        $colors = [];

        foreach ($field->options as $option) {
            if ($option->color !== null) {
                $colors[$option->value] = $option->color;
            }
        }

        return TextColumn::make($field->name)
            ->badge()
            ->formatStateUsing(static fn (mixed $state): ?string => static::optionLabel($field, $state))
            ->color(static fn (mixed $state): string => $colors[static::scalar($state)] ?? 'gray');
    }

    /**
     * @return array<string, string>
     */
    protected static function options(RecordField $field): array
    {
        $options = [];

        foreach ($field->options as $option) {
            $options[$option->value] = $option->resolvedLabel();
        }

        return $options;
    }

    protected static function optionLabel(RecordField $field, mixed $state): ?string
    {
        $value = static::scalar($state);

        if ($value === null) {
            return null;
        }

        foreach ($field->options as $option) {
            if ($option->value === $value) {
                return $option->resolvedLabel();
            }
        }

        return $value;
    }

    protected static function scalar(mixed $state): ?string
    {
        if ($state instanceof BackedEnum) {
            return (string) $state->value;
        }

        return $state === null ? null : (string) $state;
    }
}
