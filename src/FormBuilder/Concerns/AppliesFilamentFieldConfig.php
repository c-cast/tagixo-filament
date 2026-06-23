<?php

namespace Ccast\TagixoFilament\FormBuilder\Concerns;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityActionRunner;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

trait AppliesFilamentFieldConfig
{
    /**
     * Tagixo viewport → Filament breakpoint key mapping.
     */
    protected const VIEWPORT_MAP = [
        'mobile' => 'default',
        'tablet_portrait' => 'sm',
        'tablet_landscape' => 'md',
        'desktop' => 'lg',
        'ultrawide' => 'xl',
    ];

    /**
     * @template T of Field
     *
     * @param  T  $component
     * @param  array<string, mixed>  $field
     * @return T
     */
    protected static function applyCommonFieldConfig(Field $component, array $field): Field
    {
        $label = trim((string) ($field['label'] ?? ''));
        if ($label !== '') {
            $component->label($label);
            if (method_exists($component, 'validationAttribute')) {
                $component->validationAttribute($label);
            }
        }

        $helperText = $field['helper_text'] ?? null;
        if (is_string($helperText) && trim($helperText) !== '' && method_exists($component, 'helperText')) {
            $component->helperText($helperText);
        }

        $placeholder = $field['placeholder'] ?? null;
        if (is_string($placeholder) && trim($placeholder) !== '' && method_exists($component, 'placeholder')) {
            $component->placeholder($placeholder);
        }

        if (array_key_exists('default_value', $field)) {
            $component->default($field['default_value']);
        }

        $component = static::applyValidationConfig(
            $component,
            Arr::get($field, 'validation', []),
        );

        $customMessage = Arr::get($field, 'validation.custom_message');
        if (is_string($customMessage) && trim($customMessage) !== '' && method_exists($component, 'validationMessages')) {
            $component->validationMessages([
                'required' => $customMessage,
            ]);
        }

        $component = static::applyReactivityConfig($component, $field);

        return static::applyLayoutConfig($component, $field);
    }

    /**
     * @template T of Field
     *
     * @param  T  $component
     * @param  array<string, mixed>|mixed  $validation
     * @return T
     */
    protected static function applyValidationConfig(Field $component, mixed $validation): Field
    {
        if (! is_array($validation)) {
            return $component;
        }

        if (! empty($validation['required'])) {
            $component->required();
        }

        if (! empty($validation['nullable'])) {
            $component->nullable();
        }

        if (is_numeric($validation['min_length'] ?? null) && method_exists($component, 'minLength')) {
            $component->minLength((int) $validation['min_length']);
        }

        if (is_numeric($validation['max_length'] ?? null) && method_exists($component, 'maxLength')) {
            $component->maxLength((int) $validation['max_length']);
        }

        if (is_string($validation['pattern'] ?? null) && trim($validation['pattern']) !== '' && method_exists($component, 'regex')) {
            $component->regex($validation['pattern']);
        }

        if (! empty($validation['email'])) {
            $component->rule('email');
        }

        if (! empty($validation['url'])) {
            $component->rule('url');
        }

        $stringType = (string) ($validation['string_type'] ?? 'none');
        if (in_array($stringType, ['alpha', 'alpha_num', 'alpha_dash'], true)) {
            $component->rule($stringType);
        }

        if (is_string($validation['starts_with'] ?? null) && trim($validation['starts_with']) !== '') {
            $component->rule('starts_with:'.$validation['starts_with']);
        }

        if (is_string($validation['ends_with'] ?? null) && trim($validation['ends_with']) !== '') {
            $component->rule('ends_with:'.$validation['ends_with']);
        }

        return $component;
    }

    /**
     * @template T of Component
     *
     * @param  T  $component
     * @param  array<string, mixed>  $field
     * @return T
     */
    protected static function applyLayoutConfig(Component $component, array $field): Component
    {
        $responsiveSpan = static::buildResponsiveColumnSpan($field);

        if ($responsiveSpan !== null && method_exists($component, 'columnSpan')) {
            $component->columnSpan($responsiveSpan);
        }

        $component = static::applyFilamentSlots($component, $field);

        return $component;
    }

    /**
     * Apply Tagixo reactivity settings (live mode + data-driven state hooks)
     * from the `reactivity` prop block. Dispatches the action list to
     * ReactivityActionRunner inside each Filament state-hook closure.
     *
     * @template T of Field
     *
     * @param  T  $component
     * @param  array<string, mixed>  $field
     * @return T
     */
    protected static function applyReactivityConfig(Field $component, array $field): Field
    {
        $reactivity = $field['reactivity'] ?? null;

        if (! is_array($reactivity) || empty($reactivity)) {
            return $component;
        }

        $live = (bool) ($reactivity['live'] ?? false);
        if ($live && method_exists($component, 'live')) {
            $mode = (string) ($reactivity['live_mode'] ?? 'instant');
            $onBlur = $mode === 'on_blur';
            $debounce = null;
            if ($mode === 'debounced') {
                $rawDebounce = $reactivity['debounce'] ?? null;
                if (is_numeric($rawDebounce)) {
                    $debounce = (int) $rawDebounce;
                }
            }
            $component->live(onBlur: $onBlur, debounce: $debounce);
        }

        $hooks = [
            'on_state_updated' => 'afterStateUpdated',
            'on_state_hydrated' => 'afterStateHydrated',
            'before_dehydrated' => 'beforeStateDehydrated',
        ];

        foreach ($hooks as $hookKey => $methodName) {
            $actions = $reactivity[$hookKey] ?? null;
            if (! is_array($actions) || empty($actions)) {
                continue;
            }

            if (! method_exists($component, $methodName)) {
                continue;
            }

            $component->{$methodName}(function (mixed $state, Set $set, Get $get) use ($actions): void {
                ReactivityActionRunner::run($actions, $state, $set, $get);
            });
        }

        return $component;
    }

    /**
     * Apply Filament positional slots (aboveLabel/belowLabel/beforeLabel/afterLabel,
     * aboveContent/belowContent/beforeContent/afterContent, aboveErrorMessage/
     * belowErrorMessage) from the Tagixo `layout` prop.
     *
     * @template T of Component
     *
     * @param  T  $component
     * @param  array<string, mixed>  $field
     * @return T
     */
    protected static function applyFilamentSlots(Component $component, array $field): Component
    {
        $layout = $field['layout'] ?? null;

        if (! is_array($layout) || empty($layout)) {
            return $component;
        }

        $mapping = [
            'above_label' => 'aboveLabel',
            'below_label' => 'belowLabel',
            'before_label' => 'beforeLabel',
            'after_label' => 'afterLabel',
            'above_content' => 'aboveContent',
            'below_content' => 'belowContent',
            'before_content' => 'beforeContent',
            'after_content' => 'afterContent',
            'above_error_message' => 'aboveErrorMessage',
            'below_error_message' => 'belowErrorMessage',
        ];

        foreach (['label_slots', 'content_slots', 'error_slots'] as $slotGroup) {
            $slots = $layout[$slotGroup] ?? null;
            if (! is_array($slots)) {
                continue;
            }

            foreach ($slots as $key => $rawValue) {
                if (! isset($mapping[$key])) {
                    continue;
                }

                if (! is_string($rawValue)) {
                    continue;
                }

                $trimmed = trim($rawValue);
                if ($trimmed === '') {
                    continue;
                }

                $methodName = $mapping[$key];
                if (method_exists($component, $methodName)) {
                    $component->{$methodName}(new HtmlString($trimmed));
                }
            }
        }

        return $component;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected static function resolveStatePath(array $field): ?string
    {
        $name = trim((string) ($field['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $label = trim((string) ($field['label'] ?? ''));
        if ($label !== '') {
            $slug = Str::snake($label);
            if ($slug !== '') {
                return $slug;
            }
        }

        $type = trim((string) ($field['type'] ?? 'field'));
        $hash = substr(md5(json_encode($field)), 0, 8);

        return "{$type}_{$hash}";
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, string>
     */
    protected static function resolveOptions(
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): array {
        $optionsSource = (string) ($field['options_source'] ?? '');

        if ($optionsSource === 'model' || isset($field['model_options'])) {
            return static::resolveModelOptions($field, $modelRegistry, $modelOptionsResolver);
        }

        $options = $field['options'] ?? [];

        return static::normalizeOptions($options);
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, string>
     */
    protected static function resolveModelOptions(
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): array {
        $modelOptions = Arr::get($field, 'model_options', []);
        $modelKey = (string) ($modelOptions['model_key'] ?? '');
        $labelAttribute = $modelOptions['label_attribute'] ?? null;
        $valueAttribute = $modelOptions['value_attribute'] ?? null;

        if ($modelKey === '') {
            return [];
        }

        if ($modelOptionsResolver) {
            try {
                $resolved = ($modelOptionsResolver)(
                    $modelKey,
                    is_string($labelAttribute) ? $labelAttribute : null,
                    is_string($valueAttribute) ? $valueAttribute : null,
                    $field,
                );

                return static::normalizeOptions($resolved);
            } catch (Throwable) {
                return [];
            }
        }

        $model = $modelRegistry->resolveModel($modelKey);
        if ($model === null) {
            return [];
        }

        $modelClass = $model['class'] ?? null;
        if (! is_string($modelClass) || ! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return [];
        }

        $labelColumn = is_string($labelAttribute) && $labelAttribute !== ''
            ? $labelAttribute
            : 'id';
        $valueColumn = is_string($valueAttribute) && $valueAttribute !== ''
            ? $valueAttribute
            : 'id';

        try {
            /** @var class-string<Model> $modelClass */
            $options = $modelClass::query()
                ->pluck($labelColumn, $valueColumn)
                ->mapWithKeys(fn ($label, $value): array => [(string) $value => (string) $label])
                ->all();

            return is_array($options) ? $options : [];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    protected static function normalizeOptions(mixed $options): array
    {
        if ($options instanceof Collection) {
            $options = $options->all();
        }

        if (! is_array($options)) {
            return [];
        }

        $normalized = [];

        if (! array_is_list($options)) {
            foreach ($options as $value => $label) {
                if (is_array($label)) {
                    continue;
                }

                $normalized[(string) $value] = (string) $label;
            }

            return $normalized;
        }

        foreach ($options as $option) {
            if (is_array($option)) {
                $value = Arr::get($option, 'value');
                $label = Arr::get($option, 'label', $value);

                if ($value === null) {
                    continue;
                }

                $normalized[(string) $value] = (string) $label;

                continue;
            }

            if (is_scalar($option)) {
                $normalized[(string) $option] = (string) $option;
            }
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    protected static function normalizeMultipleDefault(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_map(static fn ($item): string => (string) $item, $value));
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return array_values(array_map(static fn ($item): string => (string) $item, $decoded));
                }
            }

            if (str_contains($trimmed, ',')) {
                return static::splitCsv($trimmed);
            }

            return [$trimmed];
        }

        if ($value === null) {
            return [];
        }

        return [(string) $value];
    }

    protected static function normalizeColumnSpan(mixed $columnSpan, mixed $width = null): ?int
    {
        if (is_numeric($columnSpan)) {
            $span = (int) $columnSpan;

            return $span >= 1 ? min(12, $span) : null;
        }

        if (is_numeric($width)) {
            $span = (int) $width;

            return $span >= 1 ? min(12, $span) : null;
        }

        if (is_string($width)) {
            $trimmed = trim($width);

            if (preg_match('/^(\d{1,3})%$/', $trimmed, $matches) === 1) {
                $percentage = (int) $matches[1];
                $percentage = max(1, min(100, $percentage));
                $span = (int) round(($percentage / 100) * 12);

                return max(1, min(12, $span));
            }

            if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', $trimmed, $matches) === 1) {
                $left = (int) $matches[1];
                $right = (int) $matches[2];
                if ($left > 0 && $right > 0) {
                    $span = (int) round(($left / $right) * 12);

                    return max(1, min(12, $span));
                }
            }
        }

        return null;
    }

    protected static function normalizeColumns(mixed $columns): int
    {
        if (! is_numeric($columns)) {
            return 12;
        }

        return max(1, min(12, (int) $columns));
    }

    /**
     * Build a responsive columnSpan value from a field's base + _responsive data.
     *
     * Returns a scalar int when only the desktop value is set (backward compat),
     * or a Filament breakpoint array when multiple viewports have values.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, int>|int|null
     */
    protected static function buildResponsiveColumnSpan(array $field): array | int | null
    {
        $baseSpan = static::normalizeColumnSpan(
            $field['column_span'] ?? null,
            $field['width'] ?? null,
        );

        $responsive = $field['_responsive'] ?? null;
        if (! is_array($responsive) || empty($responsive)) {
            return $baseSpan;
        }

        $breakpoints = [];

        foreach (static::VIEWPORT_MAP as $viewport => $filamentKey) {
            if ($viewport === 'desktop') {
                if ($baseSpan !== null) {
                    $breakpoints[$filamentKey] = $baseSpan;
                }

                continue;
            }

            $vpSpan = $responsive[$viewport]['content']['column_span'] ?? null;
            if (is_numeric($vpSpan)) {
                $normalized = (int) $vpSpan;
                if ($normalized >= 1) {
                    $breakpoints[$filamentKey] = min(12, $normalized);
                }
            }
        }

        if (empty($breakpoints)) {
            return $baseSpan;
        }

        // Only desktop → return scalar for backward compat
        if (count($breakpoints) === 1 && isset($breakpoints['lg'])) {
            return $breakpoints['lg'];
        }

        // Ensure 'default' exists (Filament convention)
        if (! isset($breakpoints['default'])) {
            $breakpoints['default'] = 12;
        }

        return $breakpoints;
    }

    /**
     * Build a responsive columns value for wrapper modules.
     *
     * Returns scalar int when only base value exists, or a Filament
     * breakpoint array when multiple viewports have values.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, int>|int
     */
    protected static function buildResponsiveColumns(array $field, int $defaultColumns = 12): array | int
    {
        $baseColumns = static::normalizeColumns($field['columns'] ?? $defaultColumns);

        $responsive = $field['_responsive'] ?? null;
        if (! is_array($responsive) || empty($responsive)) {
            return $baseColumns;
        }

        $breakpoints = [];

        foreach (static::VIEWPORT_MAP as $viewport => $filamentKey) {
            if ($viewport === 'desktop') {
                $breakpoints[$filamentKey] = $baseColumns;

                continue;
            }

            $vpColumns = $responsive[$viewport]['content']['columns'] ?? null;
            if (is_numeric($vpColumns)) {
                $normalized = (int) $vpColumns;
                if ($normalized >= 1) {
                    $breakpoints[$filamentKey] = min(12, $normalized);
                }
            }
        }

        // Only desktop → return scalar for backward compat
        if (count($breakpoints) === 1 && isset($breakpoints['lg'])) {
            return $breakpoints['lg'];
        }

        // Ensure 'default' exists
        if (! isset($breakpoints['default'])) {
            $breakpoints['default'] = 1;
        }

        return $breakpoints;
    }

    /**
     * @return array<int, string>
     */
    protected static function splitCsv(string $value): array
    {
        return array_values(array_filter(
            array_map(static fn (string $part): string => trim($part), explode(',', $value)),
            static fn (string $part): bool => $part !== '',
        ));
    }
}
