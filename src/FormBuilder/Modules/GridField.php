<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;

final class GridField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $wrapperType = (string) ($field['wrapper_type'] ?? '');

        if ($wrapperType === 'grid') {
            $columns = self::buildResponsiveColumns($field, 12);
            $grid = Grid::make($columns)->schema($children);

            $gap = (string) ($field['gap'] ?? '');
            if ($gap === 'none') {
                $grid->gap(false);
            } elseif ($gap === 'small') {
                $grid->dense();
            }

            $label = trim((string) ($field['label'] ?? ''));
            if ($label !== '') {
                return self::applyLayoutConfig(
                    Fieldset::make($label)->schema([$grid])->columns(1),
                    $field,
                );
            }

            return self::applyLayoutConfig($grid, $field);
        }

        return self::applyLayoutConfig(
            Group::make()->schema($children),
            $field,
        );
    }
}
