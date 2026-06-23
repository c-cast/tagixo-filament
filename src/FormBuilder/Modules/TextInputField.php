<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\MapsFilamentFieldModule;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

final class TextInputField implements FilamentFieldModule
{
    use MapsFilamentFieldModule;

    protected static function makeFilamentComponent(
        string $name,
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $component = TextInput::make($name);
        $inputType = (string) ($field['input_type'] ?? 'text');

        if ($inputType === 'email' && method_exists($component, 'email')) {
            $component->email();
        } elseif ($inputType === 'url' && method_exists($component, 'url')) {
            $component->url();
        } elseif ($inputType === 'tel' && method_exists($component, 'tel')) {
            $component->tel();
        } elseif ($inputType === 'number' && method_exists($component, 'numeric')) {
            $component->numeric();
        }

        return $component;
    }
}
