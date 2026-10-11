<?php

namespace Tagixo\Filament\FormBuilder;

use Tagixo\Filament\FormBuilder\Contracts\FilamentFieldModule;
use Tagixo\Filament\FormBuilder\Contracts\FilamentWrapperModule;

class FilamentModuleRegistry
{
    /** @var array<string, class-string<FilamentFieldModule>> */
    protected array $fields = [];

    /** @var array<string, class-string<FilamentWrapperModule>> */
    protected array $wrappers = [];

    /**
     * @param  class-string<FilamentFieldModule>  $class
     */
    public function registerField(string $type, string $class): static
    {
        $this->fields[$type] = $class;

        return $this;
    }

    /**
     * @param  class-string<FilamentWrapperModule>  $class
     */
    public function registerWrapper(string $type, string $class): static
    {
        $this->wrappers[$type] = $class;

        return $this;
    }

    /**
     * @return class-string<FilamentFieldModule>|null
     */
    public function getField(string $type): ?string
    {
        return $this->fields[$type] ?? null;
    }

    /**
     * @return class-string<FilamentWrapperModule>|null
     */
    public function getWrapper(string $type): ?string
    {
        return $this->wrappers[$type] ?? null;
    }

    /**
     * @param  array<string, class-string<FilamentFieldModule>>  $fields
     * @param  array<string, class-string<FilamentWrapperModule>>  $wrappers
     */
    public function registerMany(array $fields = [], array $wrappers = []): static
    {
        foreach ($fields as $type => $class) {
            $this->registerField($type, $class);
        }

        foreach ($wrappers as $type => $class) {
            $this->registerWrapper($type, $class);
        }

        return $this;
    }
}
