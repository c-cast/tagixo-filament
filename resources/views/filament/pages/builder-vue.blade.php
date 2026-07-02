{{--
    Visual Builder - Vue.js Version

    This page provides the container for the Vue.js Visual Builder.
    Livewire only handles:
    - Initial data loading
    - Save persistence
    - Server-side operations (when needed)

    All UI interaction is handled by Vue.js for maximum performance.

    IMPORTANT: Livewire requires a single root element - everything must be inside the main div.
--}}

<div
    x-data="{
        initialized: false,
        init() {
            // Listen for save events from Vue
            window.addEventListener('tagixo:save', (e) => {
                $wire.saveFromVue(e.detail.structure);
            });

            // Listen for global variables save from Vue
            window.addEventListener('tagixo:save-global-variables', (e) => {
                $wire.saveGlobalVariables(e.detail.variables);
            });

            // Listen for component definition requests
            window.addEventListener('tagixo:get-component-defaults', (e) => {
                const defaults = $wire.getComponentDefaults(e.detail.type);
                window.dispatchEvent(new CustomEvent('tagixo:component-defaults', {
                    detail: { type: e.detail.type, defaults: defaults }
                }));
            });

            this.initialized = true;
        }
    }"
    class="tagixo-container h-screen flex flex-col bg-gray-100 dark:bg-gray-800"
>

    {{--
        The first-paint loading overlay lives INSIDE the Vue builder app (its
        Suspense fallback + boot overlay), which owns the single loading state
        and hides it only once the canvas is fully styled (on the
        `tagixo:styles-applied` event dispatched below). Do NOT add an overlay
        here too — it would stack a second spinner over the Vue one.
    --}}

    {{-- Vue App Mount Point --}}
    {{--
        wire:ignore is REQUIRED here. The Vue builder app mounts into this node
        and owns its entire subtree. Without it, ANY Livewire round-trip the
        builder makes (e.g. $wire.getComponentDefaults / saveFromVue / the
        media-gallery modal) re-morphs the DOM back to this server markup,
        destroying the mounted Vue app and leaving the canvas blank on the
        "Loading Visual Builder..." spinner. The data-* attributes are read once
        at mount, so freezing this subtree from Livewire diffing is correct.
    --}}
    <div
        wire:ignore
        id="tagixo-vue"
        class="flex-1 w-full min-h-0"
        data-structure="{{ json_encode($this->getStructureForVue()) }}"
        data-body-props="{{ json_encode($bodyProps ?? []) }}"
        data-available-components="{{ json_encode($this->getAvailableComponentsForVue()) }}"
        data-context="{{ $context }}"
        @if ($recordKey = $this->getRecord()?->getKey())
            data-record-id="{{ $recordKey }}"
        @endif
        data-global-variables="{{ json_encode($this->getGlobalVariablesForVue()) }}"
        data-page-attributes="{{ json_encode($this->getPageAttributesForVue()) }}"
        data-translations="{{ json_encode($this->getTranslationsForVue()) }}"
        data-available-icons="{{ json_encode($this->getAvailableIconsForVue()) }}"
        data-available-fonts="{{ json_encode($this->getAvailableFontsForVue()) }}"
        data-prop-type-registry="{{ json_encode($this->getPropTypeRegistryForVue()) }}"
        data-canvas="{{ json_encode($this->getCanvasForVue()) }}"
        @if ($previewUrl = $this->getPreviewUrl())
            data-preview-url="{{ $previewUrl }}"
        @endif
        @if ($backUrl = $this->getBackUrl())
            data-back-url="{{ $backUrl }}"
        @endif
    >
        {{-- The full-screen loading overlay above is the single loading state;
             Vue mounts into this (now empty) node and owns its subtree. --}}
    </div>

    {{-- Global Media Gallery Modal (singleton for all fields) --}}
    <livewire:media-gallery::global-media-gallery-modal wire:key="global-media-gallery" />
</div>

@push('styles')
<style>
    /* Ensure Vue app takes full height */
    #tagixo-vue {
        display: flex;
        flex-direction: column;
    }
</style>
@endpush

{{-- Livewire bridge for style updates (Vue/JS loaded by layout) --}}
@script
<script>
    // Re-initialize Vue app on SPA navigation (Livewire-specific)
    document.addEventListener('livewire:navigated', () => window.initVisualBuilder?.())

    // Listen for structure changes from Vue and update dynamic styles
    let tagixoStylesAppliedOnce = false;
    window.addEventListener('tagixo:structure-changed', async (e) => {
        try {
            // Request updated stylesheet from Livewire
            const stylesheet = await $wire.regenerateStylesheet(e.detail.structure);
            const styleEl = document.getElementById('tagixo-dynamic-styles');
            if (styleEl) {
                styleEl.textContent = stylesheet;
            }

            // Signal the loading overlay that the canvas now has its full
            // styling (including body alignment) — the first application is the
            // moment the initial "flash of unstyled layout" resolves.
            if (! tagixoStylesAppliedOnce) {
                tagixoStylesAppliedOnce = true;
                window.dispatchEvent(new CustomEvent('tagixo:styles-applied'));
            }
        } catch (error) {
            console.error('[VisualBuilder] Failed to update stylesheet:', error);
        }
    });

    // Notification for successful save
    Livewire.on('tagixo:saved', () => {
        // Dispatch success notification (Filament will handle it)
        window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: 'success',
                message: '{{ __("Saved successfully") }}'
            }
        }));
    });

    // Handle save errors
    Livewire.on('tagixo:save-error', (data) => {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: 'error',
                message: data.message || '{{ __("Error while saving") }}'
            }
        }));
    });
</script>
@endscript
