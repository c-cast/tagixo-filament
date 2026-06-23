<?php

namespace Ccast\TagixoFilament\Filament\Pages;

use Ccast\Tagixo\Concerns\InteractsWithVisualBuilder;
use Ccast\Tagixo\Contracts\BuilderPageContract;
use Ccast\TagixoFilament\Concerns\InteractsWithVisualBuilderFilament;
use Filament\Pages\Page;

/**
 * Abstract Builder Page (Standalone)
 *
 * Extend this class to create standalone pages with the visual builder.
 * For Resource pages, use FilamentVisualBuilderPage instead.
 *
 * Usage:
 * 1. Extend this class
 * 2. Implement getContext() to return 'page', 'form', 'mail', or 'pdf'
 * 3. Implement loadStructure() to load the JSON structure
 * 4. Implement saveStructure() to persist the JSON structure
 */
abstract class BuilderPage extends Page implements BuilderPageContract
{
    use InteractsWithVisualBuilder;
    use InteractsWithVisualBuilderFilament;

    protected static string $layout = 'tagixo-filament::layout';

    protected string $view = 'tagixo-filament::filament.pages.builder-vue';

    /**
     * Get the builder context
     *
     * @return string One of: 'page', 'form', 'mail', 'pdf'
     */
    abstract public function getContext(): string;

    /**
     * Load the initial structure
     *
     * @return string|null The JSON structure
     */
    abstract public function loadStructure(): ?string;

    /**
     * Save the structure
     *
     * @param  string  $structure  The JSON structure to save
     */
    abstract public function saveStructure(string $structure): void;

    /**
     * Mount the page
     */
    public function mount(int | string $record): void
    {
        $this->initializeVisualBuilder();
    }
}
