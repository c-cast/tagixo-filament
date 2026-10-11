<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Tagixo\Filament\FormBuilder\Contracts\FilamentWrapperModule;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;

final class GroupField implements FilamentWrapperModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentWrapper(
        array $field,
        array $children,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $group = Group::make()->schema($children);

        $columns = self::buildResponsiveColumns($field, 1);
        if (method_exists($group, 'columns')) {
            $group->columns($columns);
        }

        return self::applyLayoutConfig($group, $field);
    }
}
