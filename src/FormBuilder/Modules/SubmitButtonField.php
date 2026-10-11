<?php

namespace Tagixo\Filament\FormBuilder\Modules;

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Schemas\Components\Component;

final class SubmitButtonField implements FilamentFieldModule
{
    public static function toFilamentField(
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        return null;
    }
}
