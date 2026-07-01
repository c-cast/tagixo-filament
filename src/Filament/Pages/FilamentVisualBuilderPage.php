<?php

namespace Ccast\TagixoFilament\Filament\Pages;

use Ccast\Tagixo\Canvas\CanvasRegistry;
use Ccast\Tagixo\Concerns\InteractsWithVisualBuilder;
use Ccast\Tagixo\Contracts\BuilderPageContract;
use Ccast\TagixoFilament\Concerns\InteractsWithVisualBuilderFilament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;

/**
 * Abstract Filament Resource Page for Visual Builder
 *
 * Extend this class in your Resource's Pages directory to add visual builder.
 * For standalone pages (not tied to a Resource), use BuilderPage instead.
 *
 * Usage:
 * 1. Extend this class in your Resource's Pages directory
 * 2. Set protected static string $resource = YourResource::class
 * 3. Implement getContext() to return 'page', 'form', 'mail', 'pdf', or 'popup'
 * 4. Implement loadStructure() to load JSON from your model
 * 5. Implement saveStructure() to persist JSON to your model
 * 6. Register the page in YourResource::getPages()
 */
abstract class FilamentVisualBuilderPage extends ResourcePage implements BuilderPageContract, HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithSchemas;
    use InteractsWithVisualBuilder;
    use InteractsWithVisualBuilderFilament;

    protected static string $layout = 'tagixo-filament::layout';

    protected string $view = 'tagixo-filament::filament.pages.builder-vue';

    /**
     * Get the builder context
     *
     * @return string One of: 'page', 'form', 'mail', 'pdf', 'popup'
     */
    abstract public function getContext(): string;

    /**
     * Load the initial structure from your model
     *
     * @return string|null The JSON structure
     */
    abstract public function loadStructure(): ?string;

    /**
     * Save the structure to your model
     *
     * @param  string  $structure  The JSON structure to save
     */
    abstract public function saveStructure(string $structure): void;

    /**
     * Mount the page with a record
     */
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->initializeVisualBuilder();
    }

    /**
     * Authorize access to the page
     * Override this method to add custom authorization
     */
    protected function authorizeAccess(): void
    {
        // Default: no authorization check
        // Override in child classes to add checks
    }

    /**
     * Optional URL for the "Preview" button in the builder toolbar.
     *
     * When set, the Vue toolbar opens this URL directly instead of going
     * through the built-in localStorage-based preview handoff. This is
     * typically used by form builders to point at a Filament-rendered
     * preview page so reactivity and grid columns can actually be
     * exercised against a real runtime.
     */
    public function getPreviewUrl(): ?string
    {
        return null;
    }

    /**
     * URL the builder's back/exit arrow links to.
     *
     * Defaults to the owning resource's index page (the list view the user
     * came from). Override to point elsewhere (e.g. the record's edit page),
     * or return null to fall back to history.back() / window.close().
     */
    public function getBackUrl(): ?string
    {
        $resource = static::getResource();

        if (! $resource) {
            return null;
        }

        return $resource::getUrl('index');
    }

    /**
     * Canvas prop types to exclude from the builder drawer.
     *
     * In the Filament panel context, some canvas prop types are irrelevant
     * because the panel already manages that functionality. For example,
     * form submission actions are handled by the consumer's PHP code
     * (Resource create/edit, custom Livewire submit), not by the visual
     * builder's SubmitPropType.
     *
     * Override this method to customize which prop types are hidden.
     *
     * @return string[]
     */
    protected function excludedCanvasPropTypes(): array
    {
        if ($this->getContext() === 'form') {
            return ['submit'];
        }

        return [];
    }

    /**
     * Override the core trait's canvas payload to filter out excluded
     * prop types before sending to the Vue frontend.
     */
    public function getCanvasForVue(): array
    {
        $canvas = app(CanvasRegistry::class)->payloadFor(
            $this->context,
            $this->getLayoutVariant(),
        );

        $excluded = $this->excludedCanvasPropTypes();
        if (! empty($excluded)) {
            $canvas['propTypes'] = array_values(array_diff($canvas['propTypes'], $excluded));
            foreach ($excluded as $key) {
                unset($canvas['defaults'][$key]);
            }
        }

        return $canvas;
    }
}
