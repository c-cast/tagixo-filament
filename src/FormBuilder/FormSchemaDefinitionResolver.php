<?php

namespace Ccast\TagixoFilament\FormBuilder;

use Filament\Resources\Pages\Page as FilamentResourcePage;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component as LivewireComponent;
use Throwable;

class FormSchemaDefinitionResolver
{
    public static function make(): static
    {
        return new static;
    }

    /**
     * Resolve a form definition for the mapper.
     *
     * - If definition is provided as array/json, return it as-is.
     * - If definition is an int (or numeric string), load fields by forms table id.
     * - If definition is null, try automatic lookup via bindings table.
     */
    public function resolve(
        array | string | int | null $definition = null,
        ?string $targetClass = null,
        ?string $targetType = null,
        ?string $operation = null,
    ): array | string | null {
        if (is_array($definition)) {
            return $definition;
        }

        if (is_int($definition)) {
            return $this->loadFieldsByFormSchemaId($definition);
        }

        if (is_string($definition)) {
            $trimmed = trim($definition);

            // Numeric string → tgx_forms.id
            if ($trimmed !== '' && ctype_digit($trimmed)) {
                return $this->loadFieldsByFormSchemaId((int) $trimmed);
            }

            // Non-numeric, non-JSON string → tgx_forms.slug. We only attempt a
            // slug lookup when the string doesn't look like an inline JSON
            // definition (object/array), so passing a raw JSON schema string
            // keeps working unchanged. A miss falls through to returning the
            // string as-is (treated as an inline definition by the caller).
            if ($trimmed !== '' && ! $this->looksLikeJson($trimmed)) {
                $bySlug = $this->loadFieldsByFormSchemaSlug($trimmed);

                if ($bySlug !== null) {
                    return $bySlug;
                }
            }

            return $definition;
        }

        [$resolvedType, $resolvedClass, $resolvedOperation] = $this->resolveTarget(
            targetClass: $targetClass,
            targetType: $targetType,
            operation: $operation,
        );

        if (! is_string($resolvedClass) || $resolvedClass === '') {
            return null;
        }

        $formSchemaId = $this->resolveBoundFormSchemaId(
            targetType: $resolvedType,
            targetClass: $resolvedClass,
            operation: $resolvedOperation,
        );

        if ($formSchemaId === null) {
            return null;
        }

        return $this->loadFieldsByFormSchemaId($formSchemaId);
    }

    /**
     * @return array{0:string,1:string,2:?string}
     */
    protected function resolveTarget(
        ?string $targetClass = null,
        ?string $targetType = null,
        ?string $operation = null,
    ): array {
        $resolvedClass = is_string($targetClass) ? trim($targetClass) : '';
        $resolvedType = is_string($targetType) ? trim($targetType) : '';
        $resolvedOperation = is_string($operation) ? trim($operation) : null;

        if ($resolvedClass !== '') {
            if ($resolvedType === '') {
                $resolvedType = $this->inferTypeFromClass($resolvedClass);
            }

            return [$resolvedType, $resolvedClass, $resolvedOperation];
        }

        [$inferredType, $inferredClass, $inferredOperation] = $this->inferTargetFromBacktrace();

        if ($resolvedType === '') {
            $resolvedType = $inferredType;
        }

        if ($resolvedClass === '') {
            $resolvedClass = $inferredClass;
        }

        if ($resolvedOperation === null || $resolvedOperation === '') {
            $resolvedOperation = $inferredOperation;
        }

        return [$resolvedType, $resolvedClass, $resolvedOperation];
    }

    protected function inferTypeFromClass(string $class): string
    {
        if (class_exists($class) && is_subclass_of($class, Resource::class)) {
            return 'resource';
        }

        if (class_exists($class) && is_subclass_of($class, LivewireComponent::class)) {
            return 'livewire';
        }

        return 'class';
    }

    /**
     * @return array{0:string,1:string,2:?string}
     */
    protected function inferTargetFromBacktrace(): array
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($stack as $frame) {
            $class = $frame['class'] ?? null;
            if (! is_string($class) || $class === '') {
                continue;
            }

            if (class_exists($class) && is_subclass_of($class, Resource::class)) {
                return ['resource', $class, null];
            }

            if (class_exists($class) && is_subclass_of($class, FilamentResourcePage::class)) {
                $resourceClass = null;
                $operation = null;

                try {
                    if (method_exists($class, 'getResource')) {
                        $resourceClass = $class::getResource();
                    }
                } catch (Throwable) {
                    $resourceClass = null;
                }

                try {
                    if (method_exists($class, 'getResourcePageName')) {
                        $operation = $class::getResourcePageName();
                    }
                } catch (Throwable) {
                    $operation = null;
                }

                if (is_string($resourceClass) && $resourceClass !== '') {
                    return ['resource', $resourceClass, $operation];
                }

                return ['livewire', $class, $operation];
            }

            if (class_exists($class) && is_subclass_of($class, LivewireComponent::class)) {
                return ['livewire', $class, null];
            }
        }

        return ['', '', null];
    }

    protected function resolveBoundFormSchemaId(
        string $targetType,
        string $targetClass,
        ?string $operation = null,
    ): ?int {
        $bindingsTable = $this->resolveBindingsTable();
        $formsTable = $this->resolveFormsTable();

        if (
            $bindingsTable === null ||
            $formsTable === null
        ) {
            return null;
        }

        $base = DB::table($bindingsTable)
            ->where('is_active', true)
            ->where('target_class', $targetClass);

        if ($targetType !== '') {
            $base->where('target_type', $targetType);
        }

        if (is_string($operation) && $operation !== '') {
            $exact = (clone $base)
                ->where('target_operation', $operation)
                ->orderByDesc('priority')
                ->orderByDesc('id')
                ->first(['form_schema_id']);

            if ($exact && isset($exact->form_schema_id)) {
                return (int) $exact->form_schema_id;
            }
        }

        $fallback = (clone $base)
            ->whereNull('target_operation')
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->first(['form_schema_id']);

        if ($fallback && isset($fallback->form_schema_id)) {
            return (int) $fallback->form_schema_id;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    protected function loadFieldsByFormSchemaId(int $formSchemaId): ?array
    {
        $formsTable = $this->resolveFormsTable();

        if (
            $formSchemaId <= 0 ||
            $formsTable === null
        ) {
            return null;
        }

        $row = DB::table($formsTable)
            ->where('id', $formSchemaId)
            ->first();

        return $this->extractFieldsFromRow($row);
    }

    /**
     * Load a form definition by its unique tgx_forms.slug.
     *
     * @return array<int, array<string, mixed>>|null
     */
    protected function loadFieldsByFormSchemaSlug(string $slug): ?array
    {
        $formsTable = $this->resolveFormsTable();

        if ($slug === '' || $formsTable === null) {
            return null;
        }

        $row = DB::table($formsTable)
            ->where('slug', $slug)
            ->first();

        return $this->extractFieldsFromRow($row);
    }

    /**
     * Extract the ordered field components from a tgx_forms row.
     *
     * Canonical column is `fields` (the ordered field components written by
     * the builder since the content/fields split); `schema` is the legacy
     * single-blob column from pre-split / pre-rename tables. property_exists
     * guards avoid "undefined property" warnings on tables missing either.
     *
     * @return array<int, array<string, mixed>>|null
     */
    protected function extractFieldsFromRow(?object $row): ?array
    {
        if (! $row) {
            return null;
        }

        $fields = null;
        foreach (['fields', 'schema'] as $column) {
            if (property_exists($row, $column) && $row->{$column} !== null) {
                $fields = $row->{$column};
                break;
            }
        }

        if (is_array($fields)) {
            return $fields;
        }

        if (is_string($fields)) {
            $decoded = json_decode($fields, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Whether a string looks like an inline JSON object/array definition,
     * in which case it must NOT be treated as a slug.
     */
    protected function looksLikeJson(string $value): bool
    {
        $first = $value[0] ?? '';

        return $first === '{' || $first === '[';
    }

    protected function resolveFormsTable(): ?string
    {
        if (Schema::hasTable('tgx_forms')) {
            return 'tgx_forms';
        }

        if (Schema::hasTable('form_schemas')) {
            return 'form_schemas';
        }

        return null;
    }

    protected function resolveBindingsTable(): ?string
    {
        if (Schema::hasTable('tgx_form_bindings')) {
            return 'tgx_form_bindings';
        }

        if (Schema::hasTable('tagixo_filament_form_bindings')) {
            return 'tagixo_filament_form_bindings';
        }

        return null;
    }
}
