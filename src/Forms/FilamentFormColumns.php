<?php

namespace Ccast\TagixoFilament\Forms;

use Ccast\Tagixo\FormBuilder\FormModule;
use Ccast\Tagixo\Models\FormSchema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class FilamentFormColumns
{
    public static function from(string $formSlug): array
    {
        $form = FormSchema::where('slug', $formSlug)->first();

        return $form ? self::resolveColumns($form) : [];
    }

    public static function forForm(int|string $formId): array
    {
        $form = FormSchema::find($formId);

        return $form ? self::resolveColumns($form) : [];
    }

    public static function fromFields(array $fields): array
    {
        return self::resolveFields($fields);
    }

    private static function resolveColumns(FormSchema $form): array
    {
        return self::resolveFields($form->fields ?? []);
    }

    private static function resolveFields(array $fields): array
    {
        $columns = [];

        foreach ($fields as $field) {
            $typeId     = (string) ($field['type'] ?? '');
            $tableProps = $field['props']['table'] ?? [];
            $content    = FormModule::fillContentDefaults($typeId, $field['props']['content'] ?? []);

            if (! (bool) self::prop($tableProps, 'show_in_table')) {
                continue;
            }

            $fieldKey = $content['name'] ?? $field['key'] ?? $field['id'] ?? null;

            if ($fieldKey === null) {
                continue;
            }

            $rawLabel      = (string) (self::prop($tableProps, 'column_label') ?? '');
            $fallbackLabel = strip_tags((string) ($content['label'] ?? $field['label'] ?? $fieldKey));
            $columnLabel   = $rawLabel !== '' ? strip_tags($rawLabel) : $fallbackLabel;
            $columnType    = (string) (self::prop($tableProps, 'column_type') ?? 'text');

            $column = match ($columnType) {
                'boolean' => IconColumn::make($fieldKey)->boolean()->label($columnLabel),
                'badge'   => BadgeColumn::make($fieldKey)->label($columnLabel),
                'image'   => ImageColumn::make($fieldKey)->label($columnLabel),
                default   => TextColumn::make($fieldKey)->label($columnLabel),
            };

            // ── Common ──────────────────────────────────────────────────────
            if ((bool) self::prop($tableProps, 'sortable')) {
                $column->sortable();
            }

            if ((bool) self::prop($tableProps, 'searchable')) {
                $column->searchable();
            }

            if ((bool) self::prop($tableProps, 'toggleable')) {
                $column->toggleable();
            }

            $alignment = self::prop($tableProps, 'alignment');
            if ($alignment && $alignment !== 'left') {
                $column->alignment($alignment);
            }

            $tooltip = self::prop($tableProps, 'tooltip');
            if (is_string($tooltip) && $tooltip !== '') {
                $column->tooltip($tooltip);
            }

            // ── Type-specific ────────────────────────────────────────────────
            match ($columnType) {
                'text'    => self::applyText($column, $tableProps),
                'date'    => self::applyDate($column, $tableProps),
                'boolean' => self::applyBoolean($column, $tableProps),
                'badge'   => self::applyBadge($column, $tableProps),
                'image'   => self::applyImage($column, $tableProps),
                default   => null,
            };

            $columns[] = $column;
        }

        return $columns;
    }

    private static function applyText(TextColumn $column, array $p): void
    {
        $limit = (int) (self::prop($p, 'text_limit') ?? 0);
        if ($limit > 0) {
            $column->limit($limit);
        }

        match (self::prop($p, 'text_weight')) {
            'bold'     => $column->bold(),
            'semibold' => $column->semibold(),
            default    => null,
        };

        $decimals = (int) (self::prop($p, 'text_decimals') ?? 2);
        $currency = self::prop($p, 'text_currency') ?: 'EUR';

        match (self::prop($p, 'text_format')) {
            'numeric' => $column->numeric(decimals: $decimals),
            'money'   => $column->money(currency: $currency, locale: 'it_IT'),
            'since'   => $column->since(),
            default   => null,
        };

        if ((bool) self::prop($p, 'text_copyable')) {
            $column->copyable();
        }

        $url = self::prop($p, 'text_url');
        if (is_string($url) && $url !== '') {
            $column->url($url)->openUrlInNewTab((bool) (self::prop($p, 'text_open_in_new_tab') ?? true));
        }
    }

    private static function applyDate(TextColumn $column, array $p): void
    {
        if ((bool) self::prop($p, 'date_since')) {
            $column->since();
        } else {
            $column->date(self::prop($p, 'date_format') ?: 'd/m/Y H:i');
        }
    }

    private static function applyBoolean(IconColumn $column, array $p): void
    {
        $trueIcon = self::prop($p, 'bool_true_icon');
        if (is_string($trueIcon) && $trueIcon !== '') {
            $column->trueIcon($trueIcon);
        }

        $falseIcon = self::prop($p, 'bool_false_icon');
        if (is_string($falseIcon) && $falseIcon !== '') {
            $column->falseIcon($falseIcon);
        }

        $trueColor = self::prop($p, 'bool_true_color');
        if (is_string($trueColor) && $trueColor !== '') {
            $column->trueColor($trueColor);
        }

        $falseColor = self::prop($p, 'bool_false_color');
        if (is_string($falseColor) && $falseColor !== '') {
            $column->falseColor($falseColor);
        }

        $size = self::prop($p, 'bool_size');
        if (is_string($size) && $size !== '' && $size !== 'md') {
            $column->size($size);
        }
    }

    private static function applyBadge(BadgeColumn $column, array $p): void
    {
        $badgeColors = self::prop($p, 'badge_colors');
        if (is_array($badgeColors) && $badgeColors !== []) {
            $map = [];
            foreach ($badgeColors as $item) {
                if (isset($item['value'], $item['color'])) {
                    $map[$item['value']] = $item['color'];
                }
            }
            if ($map !== []) {
                $column->colors($map);
            }
        }

        $badgeIcons = self::prop($p, 'badge_icons');
        if (is_array($badgeIcons) && $badgeIcons !== []) {
            $map = [];
            foreach ($badgeIcons as $item) {
                if (isset($item['value'], $item['icon'])) {
                    $map[$item['value']] = $item['icon'];
                }
            }
            if ($map !== []) {
                $column->icons($map);
            }
        }
    }

    private static function applyImage(ImageColumn $column, array $p): void
    {
        match (self::prop($p, 'image_shape')) {
            'circular' => $column->circular(),
            'rounded'  => $column->rounded(),
            default    => null,
        };

        $height = self::prop($p, 'image_height');
        if (is_string($height) && $height !== '') {
            $column->height($height);
        }

        $size = self::prop($p, 'image_size');
        if (is_string($size) && $size !== '') {
            $column->size($size);
        }
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
