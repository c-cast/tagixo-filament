<?php

namespace Ccast\TagixoFilament\Forms;

use Ccast\Tagixo\Models\FormSchema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class FilamentFormColumns
{
    public static function from(string $formSlug): array
    {
        $form = FormSchema::where('slug', $formSlug)->first();

        if ($form === null) {
            return [];
        }

        return self::resolveColumns($form);
    }

    public static function forForm(int|string $formId): array
    {
        $form = FormSchema::find($formId);

        if ($form === null) {
            return [];
        }

        return self::resolveColumns($form);
    }

    private static function resolveColumns(FormSchema $form): array
    {
        $columns = [];

        foreach ($form->fields ?? [] as $field) {
            $tableProps = $field['props']['table'] ?? [];

            if (! (bool) ($tableProps['show_in_table']['value'] ?? false)) {
                continue;
            }

            $fieldKey = $field['key'] ?? $field['id'] ?? null;

            if ($fieldKey === null) {
                continue;
            }

            $columnLabel = (string) ($tableProps['column_label']['value'] ?? '');
            $columnLabel = $columnLabel ?: ($field['label'] ?? $fieldKey);
            $columnType  = (string) ($tableProps['column_type']['value'] ?? 'text');
            $sortable    = (bool) ($tableProps['sortable']['value'] ?? false);
            $searchable  = (bool) ($tableProps['searchable']['value'] ?? false);

            $column = match ($columnType) {
                'boolean' => IconColumn::make($fieldKey)->boolean()->label($columnLabel),
                'badge'   => TextColumn::make($fieldKey)->badge()->label($columnLabel),
                'date'    => TextColumn::make($fieldKey)->dateTime()->label($columnLabel)->since(),
                default   => TextColumn::make($fieldKey)->label($columnLabel),
            };

            if ($sortable) {
                $column = $column->sortable();
            }

            if ($searchable) {
                $column = $column->searchable();
            }

            $columns[] = $column;
        }

        return $columns;
    }
}
