<?php

namespace Ccast\TagixoFilament\Filament\Forms;

use Ccast\TagixoFilament\FormBuilder\FormSchemaDefinitionResolver;
use Ccast\TagixoFilament\FormBuilder\FormSchemaToFilamentMapper;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

/**
 * Tagixo Form bridge for Filament.
 *
 * Usage:
 * - Form::configure($schema)
 * - Form::configure($schema, $record->fields)
 * - Form::configure($schema, 12) // tgx_forms.id
 * - Form::configure($schema, $jsonDefinition)
 *
 * The definition can be:
 * - legacy builder components (flat array with parent_id + props)
 * - normalized schema ({ fields: [...] })
 * - raw JSON string of one of the above
 */
class Form
{
    /**
     * Configure a Filament schema from a Tagixo form definition.
     *
     * @param  callable|null  $modelOptionsResolver  fn (string $modelKey, ?string $labelAttribute, ?string $valueAttribute, array $field): array
     * @param  callable|null  $componentResolver  fn (array $field, FormSchemaToFilamentMapper $mapper): ?Component
     */
    public static function configure(
        Schema $schema,
        array | string | int | null $definition = null,
        ?callable $modelOptionsResolver = null,
        ?callable $componentResolver = null,
        ?string $targetClass = null,
        ?string $targetType = null,
        ?string $operation = null,
    ): Schema {
        // Resolve the raw definition once so we can inspect `body.layout.columns`
        // before feeding the flattened components array to the mapper. The
        // mapper itself only looks at `fields`/`components` and ignores `body`.
        $resolved = FormSchemaDefinitionResolver::make()->resolve(
            definition: $definition,
            targetClass: $targetClass,
            targetType: $targetType,
            operation: $operation,
        );

        $columns = static::extractBodyColumnsResponsive($resolved);
        if ($columns !== null) {
            $schema->columns($columns);
        }

        return $schema->components(
            FormSchemaToFilamentMapper::make(
                modelOptionsResolver: $modelOptionsResolver,
                componentResolver: $componentResolver,
            )->map($resolved)
        );
    }

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
     * Pull the form-level grid column count from a resolved definition.
     *
     * Returns a scalar int when only the desktop value is set, or a Filament
     * breakpoint array when multiple viewports have values. Returns null when
     * the value is missing so the caller can preserve Filament's default.
     *
     * @return array<string, int>|int|null
     */
    protected static function extractBodyColumnsResponsive(array | string | null $resolved): array | int | null
    {
        if (is_string($resolved)) {
            $decoded = json_decode($resolved, true);
            $resolved = is_array($decoded) ? $decoded : null;
        }

        if (! is_array($resolved)) {
            return null;
        }

        $baseColumns = $resolved['body']['grid']['columns'] ?? null;
        $responsive = $resolved['body']['_responsive'] ?? null;

        // No base and no responsive → null
        if (! is_numeric($baseColumns) && (! is_array($responsive) || empty($responsive))) {
            return null;
        }

        $base = null;
        if (is_numeric($baseColumns)) {
            $val = (int) $baseColumns;
            if ($val >= 1 && $val <= 12) {
                $base = $val;
            }
        }

        if (! is_array($responsive) || empty($responsive)) {
            return $base;
        }

        $breakpoints = [];

        foreach (static::VIEWPORT_MAP as $viewport => $filamentKey) {
            if ($viewport === 'desktop') {
                if ($base !== null) {
                    $breakpoints[$filamentKey] = $base;
                }

                continue;
            }

            $vpColumns = $responsive[$viewport]['grid']['columns'] ?? null;
            if (is_numeric($vpColumns)) {
                $normalized = (int) $vpColumns;
                if ($normalized >= 1 && $normalized <= 12) {
                    $breakpoints[$filamentKey] = $normalized;
                }
            }
        }

        if (empty($breakpoints)) {
            return $base;
        }

        // Only desktop → return scalar for backward compat
        if (count($breakpoints) === 1 && isset($breakpoints['lg'])) {
            return $breakpoints['lg'];
        }

        // Ensure 'default' exists (Filament convention)
        if (! isset($breakpoints['default'])) {
            $breakpoints['default'] = 1;
        }

        return $breakpoints;
    }

    /**
     * Build Filament components from a Tagixo form definition.
     *
     * @param  callable|null  $modelOptionsResolver  fn (string $modelKey, ?string $labelAttribute, ?string $valueAttribute, array $field): array
     * @param  callable|null  $componentResolver  fn (array $field, FormSchemaToFilamentMapper $mapper): ?Component
     * @return array<Component>
     */
    public static function components(
        array | string | int | null $definition = null,
        ?callable $modelOptionsResolver = null,
        ?callable $componentResolver = null,
        ?string $targetClass = null,
        ?string $targetType = null,
        ?string $operation = null,
    ): array {
        $resolvedDefinition = FormSchemaDefinitionResolver::make()->resolve(
            definition: $definition,
            targetClass: $targetClass,
            targetType: $targetType,
            operation: $operation,
        );

        return FormSchemaToFilamentMapper::make(
            modelOptionsResolver: $modelOptionsResolver,
            componentResolver: $componentResolver,
        )->map($resolvedDefinition);
    }
}
