<?php

namespace Ccast\TagixoFilament\FormBuilder\Reactivity;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * ReactivityActionRunner
 *
 * Executes data-driven reactive actions inside a Filament state hook closure
 * (afterStateUpdated / afterStateHydrated / beforeStateDehydrated).
 *
 * Actions are plain arrays stored by the visual builder in
 * $field['reactivity']['on_state_updated'] (and the two sibling keys).
 *
 * Each action dict has the shape:
 *   [
 *     'action_type'        => 'set_value' | 'clear_field' | 'copy_state' | 'set_expression',
 *     'target_field'       => string, // absolute state path
 *     'static_value'       => ?string,
 *     'expression'         => ?string, // sandboxed expression (set_expression only)
 *     'transform'          => 'none' | 'upper' | 'lower' | 'slug' | 'trim' | 'int' | 'float' | 'bool',
 *     'condition_field'    => ?string, // blank = always run
 *     'condition_operator' => '==' | '!=' | 'empty' | 'not_empty',
 *     'condition_value'    => ?string,
 *   ]
 */
final class ReactivityActionRunner
{
    /**
     * Execute a list of actions.
     *
     * @param  array<int, array<string, mixed>>  $actions
     */
    public static function run(
        array $actions,
        mixed $state,
        Set $set,
        Get $get,
    ): void {
        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            if (! self::matchesCondition($action, $get)) {
                continue;
            }

            self::runSingle($action, $state, $set, $get);
        }
    }

    /**
     * @param  array<string, mixed>  $action
     */
    private static function matchesCondition(array $action, Get $get): bool
    {
        $field = $action['condition_field'] ?? null;

        if (! is_string($field) || trim($field) === '') {
            return true;
        }

        $operator = (string) ($action['condition_operator'] ?? '==');
        $expected = $action['condition_value'] ?? null;

        $actual = $get(trim($field));

        return match ($operator) {
            '==' => self::looseEquals($actual, $expected),
            '!=' => ! self::looseEquals($actual, $expected),
            'empty' => blank($actual),
            'not_empty' => filled($actual),
            default => true,
        };
    }

    private static function looseEquals(mixed $actual, mixed $expected): bool
    {
        if (is_bool($actual) || is_bool($expected)) {
            return (bool) $actual === self::toBool($expected);
        }

        if (is_numeric($actual) && is_numeric($expected)) {
            return (float) $actual === (float) $expected;
        }

        return (string) ($actual ?? '') === (string) ($expected ?? '');
    }

    /**
     * @param  array<string, mixed>  $action
     */
    private static function runSingle(array $action, mixed $state, Set $set, Get $get): void
    {
        $target = $action['target_field'] ?? null;

        if (! is_string($target) || trim($target) === '') {
            return;
        }

        $target = trim($target);
        $type = (string) ($action['action_type'] ?? '');
        $transform = (string) ($action['transform'] ?? 'none');

        switch ($type) {
            case 'set_value':
                $value = self::applyTransform($action['static_value'] ?? null, $transform);
                $set($target, $value);

                break;

            case 'clear_field':
                $set($target, null);

                break;

            case 'copy_state':
                $set($target, self::applyTransform($state, $transform));

                break;

            case 'set_expression':
                $expression = (string) ($action['expression'] ?? '');
                if (trim($expression) === '') {
                    break;
                }
                $computed = ReactivityExpressionEvaluator::evaluate($expression, $state, $get);
                $set($target, self::applyTransform($computed, $transform));

                break;
        }
    }

    private static function applyTransform(mixed $value, string $transform): mixed
    {
        if ($transform === '' || $transform === 'none') {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return match ($transform) {
            'upper' => Str::upper((string) $value),
            'lower' => Str::lower((string) $value),
            'slug' => Str::slug((string) $value),
            'trim' => trim((string) $value),
            'int' => is_numeric($value) ? (int) $value : (int) (string) $value,
            'float' => is_numeric($value) ? (float) $value : (float) (string) $value,
            'bool' => self::toBool($value),
            default => $value,
        };
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value !== 0.0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return ! in_array($normalized, ['', '0', 'false', 'no', 'off', 'null'], true);
        }

        return (bool) $value;
    }
}
