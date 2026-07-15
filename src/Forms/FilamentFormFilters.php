<?php

namespace Ccast\TagixoFilament\Forms;

use Ccast\Tagixo\FormBuilder\FormModule;
use Ccast\Tagixo\Models\FormSchema;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class FilamentFormFilters
{
    public static function from(string $formSlug): array
    {
        $form = FormSchema::where('slug', $formSlug)->first();

        return $form ? self::resolveFilters($form) : [];
    }

    public static function forForm(int|string $formId): array
    {
        $form = FormSchema::find($formId);

        return $form ? self::resolveFilters($form) : [];
    }

    private static function resolveFilters(FormSchema $form): array
    {
        $filters = [];

        foreach ($form->fields ?? [] as $field) {
            $typeId     = (string) ($field['type'] ?? '');
            $tableProps = $field['props']['table'] ?? [];
            $content    = FormModule::fillContentDefaults($typeId, $field['props']['content'] ?? []);

            if (! (bool) self::prop($tableProps, 'show_in_table')) {
                continue;
            }

            if (! (bool) self::prop($tableProps, 'filterable')) {
                continue;
            }

            $fieldKey = $content['name'] ?? $field['key'] ?? $field['id'] ?? null;

            if ($fieldKey === null) {
                continue;
            }

            $rawLabel      = (string) (self::prop($tableProps, 'column_label') ?? '');
            $fallbackLabel = strip_tags((string) ($field['props']['content']['label'] ?? $field['label'] ?? $fieldKey));
            $columnLabel   = $rawLabel !== '' ? strip_tags($rawLabel) : $fallbackLabel;
            $columnType    = (string) (self::prop($tableProps, 'column_type') ?? 'text');

            $filter = match ($columnType) {
                'boolean' => TernaryFilter::make($fieldKey)
                    ->label($columnLabel)
                    ->trueLabel(__('Yes'))
                    ->falseLabel(__('No')),

                'badge' => self::buildBadgeFilter($fieldKey, $columnLabel, $tableProps),

                'image' => null,

                default => Filter::make($fieldKey)->label($columnLabel),
            };

            if ($filter !== null) {
                $filters[] = $filter;
            }
        }

        return $filters;
    }

    private static function buildBadgeFilter(string $fieldKey, string $columnLabel, array $p): SelectFilter
    {
        $filter = SelectFilter::make($fieldKey)->label($columnLabel)->multiple();

        $badgeColors = self::prop($p, 'badge_colors');
        if (is_array($badgeColors) && $badgeColors !== []) {
            $options = [];
            foreach ($badgeColors as $item) {
                if (isset($item['value'])) {
                    $options[$item['value']] = $item['value'];
                }
            }
            if ($options !== []) {
                $filter->options($options);
            }
        }

        return $filter;
    }

    private static function prop(array $tableProps, string $key): mixed
    {
        if (! array_key_exists($key, $tableProps)) {
            return null;
        }

        $v = $tableProps[$key];

        return is_array($v) && array_key_exists('value', $v) ? $v['value'] : $v;
    }
}
