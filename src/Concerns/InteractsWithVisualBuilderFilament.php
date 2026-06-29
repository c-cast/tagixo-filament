<?php

namespace Ccast\TagixoFilament\Concerns;

use Ccast\Tagixo\Core\StyleGenerator;
use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\Tagixo\Services\BuilderApiService;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

/**
 * Filament/Livewire bridge for InteractsWithVisualBuilder
 *
 * Implements the abstract methods from the core trait using Filament Notifications
 * and Livewire dispatch/skipRender. Also re-adds the Livewire #[On] event handlers
 * that delegate to the core builder methods.
 *
 * Pages using the visual builder should use both traits:
 *   use InteractsWithVisualBuilder;          // core logic
 *   use InteractsWithVisualBuilderFilament;  // Filament/Livewire bridge
 */
trait InteractsWithVisualBuilderFilament
{
    protected function notifySuccess(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->success()
            ->title($title);

        if ($body !== null) {
            $notification->body($body);
        }

        $notification->send();
    }

    protected function notifyError(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->danger()
            ->title($title);

        if ($body !== null) {
            $notification->body($body);
        }

        $notification->send();
    }

    protected function dispatchBuilderEvent(string $event, mixed ...$params): void
    {
        $this->dispatch($event, ...$params);
    }

    protected function skipBuilderRender(): void
    {
        $this->skipRender();
    }

    /**
     * URL the builder's back/exit arrow links to.
     *
     * Null by default (standalone pages fall back to history.back() /
     * window.close()). Resource-backed builder pages override this to return
     * the owning resource's index URL — see FilamentVisualBuilderPage.
     */
    public function getBackUrl(): ?string
    {
        return null;
    }

    /**
     * Regenerate stylesheet for live canvas preview.
     * Called from the Vue frontend via $wire.regenerateStylesheet(structure).
     */
    public function regenerateStylesheet(string | array $structure): string
    {
        $parsed = is_array($structure) ? $structure : json_decode($structure, true);

        if (! is_array($parsed)) {
            return '';
        }

        // Prefer canonical core service when available.
        if (class_exists(BuilderApiService::class)) {
            return app(BuilderApiService::class)->stylesheet($parsed);
        }

        // Fallback for older core versions.
        $components = $parsed['components'] ?? [];
        $renderer = app(PageRenderer::class);
        $globalVarsCss = $renderer->generateGlobalVariablesCss();
        $componentCss = StyleGenerator::generateAllStyles($components);

        return trim(($globalVarsCss ? $globalVarsCss."\n" : '').$componentCss);
    }

    // =========================================================================
    // LIVEWIRE EVENT HANDLERS
    // =========================================================================

    #[On('add-child-component')]
    public function handleAddChildComponent(string $type, string $parentId): void
    {
        $this->addChildComponent($type, $parentId);
    }

    #[On('open-component-picker')]
    public function handleOpenComponentPicker(string $parentId): void
    {
        $this->openComponentPicker($parentId);
    }

    #[On('select-component')]
    public function handleSelectComponent(string $componentId): void
    {
        $this->selectComponent($componentId);
    }

    #[On('duplicate-component')]
    public function handleDuplicateComponent(string $componentId): void
    {
        $this->duplicateComponent($componentId);
    }

    #[On('remove-component')]
    public function handleRemoveComponent(string $componentId): void
    {
        $this->removeComponent($componentId);
    }

    #[On('add-root-component')]
    public function handleAddRootComponent(string $type): void
    {
        $this->addRootComponent($type);
    }

    #[On('save-component-props')]
    public function handleSaveComponentProps(string $componentId, array $props): void
    {
        $this->saveComponentProps($componentId, $props);
    }

    #[On('save-body-props')]
    public function handleSaveBodyProps(array $props): void
    {
        $this->saveBodyProps($props);
    }

    #[On('body-props-updated')]
    public function handleBodyPropsUpdated(array $props): void
    {
        $this->updateBodyPropsPreview($props);
    }

    #[On('drawer-closed')]
    public function handleDrawerClosed(): void
    {
        $this->onDrawerClosed();
    }
}
