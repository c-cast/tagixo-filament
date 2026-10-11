<?php

namespace Tagixo\Filament\FormBuilder\Reactivity;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Tagixo\FormBuilder\Reactivity\FormState;

/**
 * The form being filled in, as Filament hands it to a state hook. What a
 * reactivity rule means belongs to the form builder; reaching the fields of
 * *this* form is the panel's part, and here it is two lines.
 */
class FilamentFormState implements FormState
{
    public function __construct(
        protected Get $get,
        protected Set $set,
    ) {}

    public function read(string $path): mixed
    {
        return ($this->get)($path);
    }

    public function write(string $path, mixed $value): void
    {
        ($this->set)($path, $value);
    }
}
