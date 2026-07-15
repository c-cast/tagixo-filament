<?php

namespace Ccast\TagixoFilament\FormBuilder;

use Ccast\Tagixo\Core\ComponentRegistry;
use Ccast\Tagixo\FormBuilder\FormModule;
use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Str;
use Throwable;

class FormSchemaToFilamentMapper
{
    use AppliesFilamentFieldConfig;

    public function __construct(
        protected ComponentRegistry $componentRegistry,
        protected BuilderModelRegistryService $modelRegistry,
        protected FilamentModuleRegistry $filamentModuleRegistry,
        ?callable $modelOptionsResolver = null,
        ?callable $componentResolver = null,
    ) {
        $this->modelOptionsResolver = $modelOptionsResolver
            ? Closure::fromCallable($modelOptionsResolver)
            : null;
        $this->componentResolver = $componentResolver
            ? Closure::fromCallable($componentResolver)
            : null;
    }

    protected ?Closure $modelOptionsResolver = null;

    protected ?Closure $componentResolver = null;

    /**
     * @param  callable|null  $modelOptionsResolver  fn (string $modelKey, ?string $labelAttribute, ?string $valueAttribute, array $field): array
     * @param  callable|null  $componentResolver  fn (array $field, FormSchemaToFilamentMapper $mapper): ?Component
     */
    public static function make(
        ?callable $modelOptionsResolver = null,
        ?callable $componentResolver = null,
    ): static {
        return new static(
            componentRegistry: app(ComponentRegistry::class),
            modelRegistry: app(BuilderModelRegistryService::class),
            filamentModuleRegistry: app(FilamentModuleRegistry::class),
            modelOptionsResolver: $modelOptionsResolver,
            componentResolver: $componentResolver,
        );
    }

    /**
     * @return array<Component>
     */
    public function map(array | string | null $definition): array
    {
        $schemaFields = $this->normalizeToSchemaFields($definition);

        return $this->mapSchemaFields($schemaFields);
    }

    /**
     * @param  array<int, array<string, mixed>>  $schemaFields
     * @return array<Component>
     */
    protected function mapSchemaFields(array $schemaFields): array
    {
        $components = [];

        foreach ($schemaFields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $component = $this->mapSchemaField($field);
            if (! $component instanceof Component) {
                continue;
            }

            $components[] = $component;
        }

        return $components;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function mapSchemaField(array $field): ?Component
    {
        if ($this->componentResolver) {
            $custom = ($this->componentResolver)($field, $this);
            if ($custom === false) {
                return null;
            }

            if ($custom instanceof Component) {
                return static::applyLayoutConfig($custom, $field);
            }
        }

        $type = (string) ($field['type'] ?? '');

        // Wrapper delegation
        if ($type === 'wrapper') {
            $wrapperType = (string) ($field['wrapper_type'] ?? '');
            $wrapperClass = $this->filamentModuleRegistry->getWrapper($wrapperType);

            if ($wrapperClass === null) {
                $wrapperClass = $this->filamentModuleRegistry->getWrapper('grid');
            }

            if ($wrapperClass !== null) {
                $children = $this->mapSchemaFields($this->sortByOrder($field['children'] ?? []));

                return $wrapperClass::toFilamentWrapper(
                    $field,
                    $children,
                    $this->modelRegistry,
                    $this->modelOptionsResolver,
                );
            }

            return null;
        }

        // Field delegation
        $fieldClass = $this->filamentModuleRegistry->getField($type);
        if ($fieldClass !== null) {
            return $fieldClass::toFilamentField(
                $field,
                $this->modelRegistry,
                $this->modelOptionsResolver,
            );
        }

        // Fallback for unregistered types
        return $this->mapFallbackField($field);
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function mapFallbackField(array $field): ?Component
    {
        $name = static::resolveStatePath($field);
        if ($name === null) {
            return null;
        }

        $component = TextInput::make($name);

        if (empty($field['label']) && ! empty($field['type'])) {
            $component->label(Str::headline((string) $field['type']));
        }

        return static::applyCommonFieldConfig($component, $field);
    }

    // ──────────────────────────────────────────────────────────
    // Schema normalization & tree reconstruction
    // ──────────────────────────────────────────────────────────

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeToSchemaFields(array | string | null $definition): array
    {
        $decoded = $this->decodeDefinition($definition);
        if ($decoded === []) {
            return [];
        }

        $payload = $decoded;

        if (isset($payload['fields']) && is_array($payload['fields'])) {
            $payload = $payload['fields'];
        } elseif (isset($payload['components']) && is_array($payload['components'])) {
            $payload = $payload['components'];
        }

        if (! is_array($payload)) {
            return [];
        }

        if (! array_is_list($payload)) {
            return [];
        }

        $payload = array_values(array_filter($payload, 'is_array'));
        if ($payload === []) {
            return [];
        }

        if ($this->looksLikeBuilderComponentList($payload)) {
            return $this->schemaFromBuilderComponents($payload);
        }

        return $this->sortSchemaTree($payload);
    }

    /**
     * @param  array<string, mixed>|array|string|null  $definition
     * @return array<string, mixed>
     */
    protected function decodeDefinition(array | string | null $definition): array
    {
        if (is_string($definition)) {
            $decoded = json_decode($definition, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($definition)) {
            return $definition;
        }

        return [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function looksLikeBuilderComponentList(array $items): bool
    {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (
                array_key_exists('props', $item) ||
                array_key_exists('parent_id', $item)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @return array<int, array<string, mixed>>
     */
    protected function schemaFromBuilderComponents(array $components): array
    {
        $isFlat = collect($components)->contains(fn (array $component): bool => array_key_exists('parent_id', $component));

        if (! $isFlat) {
            return $this->schemaFromTreeComponents($components);
        }

        $indexed = [];
        foreach ($components as $index => $component) {
            $id = (string) ($component['id'] ?? "idx-{$index}");
            $component['_id'] = $id;
            $indexed[$id] = $component;
        }

        $roots = [];
        $childrenMap = [];

        foreach ($indexed as $id => $component) {
            $parentId = $component['parent_id'] ?? null;
            if ($parentId === null || ! isset($indexed[$parentId])) {
                $roots[] = $component;

                continue;
            }

            $childrenMap[$parentId][] = $component;
        }

        $resolveItems = function (array $items) use (&$resolveItems, $childrenMap): array {
            $items = $this->sortByOrder($items);
            $result = [];

            foreach ($items as $component) {
                $id = (string) ($component['_id'] ?? '');
                $children = $childrenMap[$id] ?? [];

                $schema = $this->schemaFromComponent($component, $resolveItems($children));
                if ($schema !== null) {
                    $result[] = $schema;
                }
            }

            return $result;
        };

        return $resolveItems($roots);
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @return array<int, array<string, mixed>>
     */
    protected function schemaFromTreeComponents(array $components): array
    {
        $result = [];

        foreach ($this->sortByOrder($components) as $component) {
            $children = $this->schemaFromTreeComponents($component['children'] ?? []);
            $schema = $this->schemaFromComponent($component, $children);

            if ($schema !== null) {
                $result[] = $schema;
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $component
     * @param  array<int, array<string, mixed>>  $children
     * @return array<string, mixed>|null
     */
    protected function schemaFromComponent(array $component, array $children = []): ?array
    {
        $typeId = (string) ($component['type'] ?? '');
        if ($typeId === '') {
            return null;
        }

        $componentClass = $this->componentRegistry->get($typeId);
        $metadata = $this->componentRegistry->getMetadata($typeId) ?? [];
        $props = is_array($component['props'] ?? null) ? $component['props'] : [];

        $isWrapper = (bool) ($metadata['is_wrapper'] ?? false);
        $schema = null;

        if ($componentClass && method_exists($componentClass, 'toSchema')) {
            try {
                if ($isWrapper) {
                    $schema = $componentClass::toSchema($props, $children);
                } else {
                    $schema = $componentClass::toSchema($props);
                }
            } catch (Throwable) {
                $schema = null;
            }
        }

        if (! is_array($schema)) {
            $schema = $this->fallbackSchemaFromComponent(
                $typeId,
                $metadata,
                $props,
                $children,
            );
        }

        $content = $this->extractContent($componentClass, $props);
        $content = FormModule::fillContentDefaults($typeId, $content);
        if (is_numeric($content['column_span'] ?? null) && ! isset($schema['column_span'])) {
            $schema['column_span'] = (int) $content['column_span'];
        }

        if (isset($props['_responsive']) && is_array($props['_responsive']) && ! isset($schema['_responsive'])) {
            $schema['_responsive'] = $props['_responsive'];
        }

        if (isset($component['width']) && ! isset($schema['width'])) {
            $schema['width'] = $component['width'];
        }

        if (! $isWrapper && isset($component['group']) && ! isset($schema['group'])) {
            $schema['group'] = $component['group'];
        }

        if (! $isWrapper && is_array($children) && ! empty($children) && ! isset($schema['children'])) {
            $schema['children'] = $children;
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    protected function extractContent(?string $componentClass, array $props): array
    {
        if ($componentClass && method_exists($componentClass, 'extractContent')) {
            try {
                $content = $componentClass::extractContent($props);

                return is_array($content) ? $content : [];
            } catch (Throwable) {
                return [];
            }
        }

        return is_array($props['content'] ?? null)
            ? $props['content']
            : $props;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $props
     * @param  array<int, array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    protected function fallbackSchemaFromComponent(
        string $typeId,
        array $metadata,
        array $props,
        array $children = [],
    ): array {
        $content = $this->extractContent(null, $props);

        if (($metadata['is_wrapper'] ?? false) === true) {
            return [
                'type' => 'wrapper',
                'wrapper_type' => (string) ($metadata['field_type'] ?? $typeId),
                'label' => $content['label'] ?? null,
                'columns' => $content['columns'] ?? 12,
                'gap' => $content['gap'] ?? 'medium',
                'children' => $children,
            ];
        }

        return [
            'type' => (string) ($metadata['field_type'] ?? $typeId),
            'name' => $content['name'] ?? null,
            'label' => $content['label'] ?? null,
            'placeholder' => $content['placeholder'] ?? null,
            'helper_text' => $content['helper_text'] ?? null,
            'default_value' => $content['default_value'] ?? null,
            'validation' => is_array($content['validation'] ?? null) ? $content['validation'] : [],
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Sorting helpers
    // ──────────────────────────────────────────────────────────

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function sortByOrder(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $rows = array_values(array_filter($items, 'is_array'));

        usort(
            $rows,
            fn (array $a, array $b): int => (int) ($a['order'] ?? 0) <=> (int) ($b['order'] ?? 0),
        );

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $schemaFields
     * @return array<int, array<string, mixed>>
     */
    protected function sortSchemaTree(array $schemaFields): array
    {
        $schemaFields = $this->sortByOrder($schemaFields);

        return array_map(function (array $field): array {
            if (is_array($field['children'] ?? null)) {
                $field['children'] = $this->sortSchemaTree($field['children']);
            }

            // Simple-format fields are flat (no props/parent_id), so fillContentDefaults
            // is never called upstream. Backfill column_span here so both formats
            // behave consistently when the key is absent (default = 12, full width).
            if (! isset($field['column_span'])) {
                $typeId  = (string) ($field['type'] ?? '');
                $filled  = $typeId !== '' ? FormModule::fillContentDefaults($typeId, $field) : $field;
                if (isset($filled['column_span'])) {
                    $field['column_span'] = $filled['column_span'];
                }
            }

            return $field;
        }, $schemaFields);
    }
}
