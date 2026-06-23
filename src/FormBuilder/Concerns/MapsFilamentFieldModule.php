<?php

namespace Ccast\TagixoFilament\FormBuilder\Concerns;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;

/**
 * Template trait for FilamentFieldModule implementations.
 *
 * Reduces boilerplate to:
 * - build a Filament component from the resolved field name
 * - optionally apply advanced, field-specific adjustments
 */
trait MapsFilamentFieldModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentField(
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $name = static::resolveStatePath($field);
        if ($name === null) {
            return null;
        }

        $component = static::makeFilamentComponent(
            $name,
            $field,
            $modelRegistry,
            $modelOptionsResolver,
        );

        if (! $component instanceof Component) {
            return null;
        }

        if ($component instanceof Field) {
            $component = static::applyCommonFieldConfig($component, $field);
        } else {
            $component = static::applyLayoutConfig($component, $field);
        }

        return static::afterFilamentComponentBuilt(
            $component,
            $field,
            $modelRegistry,
            $modelOptionsResolver,
        );
    }

    /**
     * @param  array<string, mixed>  $field
     */
    abstract protected static function makeFilamentComponent(
        string $name,
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component;

    /**
     * @param  array<string, mixed>  $field
     */
    protected static function afterFilamentComponentBuilt(
        Component $component,
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): Component {
        return $component;
    }
}
