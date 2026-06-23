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

    {{-- Vue App Mount Point --}}
    <div
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
    >
        {{-- Loading State (shown until Vue mounts) --}}
        <div class="h-full flex items-center justify-center" x-show="!initialized">
            <div class="text-center">
                <svg class="animate-spin h-10 w-10 text-primary-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">{{ __('Loading Visual Builder...') }}</p>
            </div>
        </div>
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
    window.addEventListener('tagixo:structure-changed', async (e) => {
        try {
            // Request updated stylesheet from Livewire
            const stylesheet = await $wire.regenerateStylesheet(e.detail.structure);
            const styleEl = document.getElementById('tagixo-dynamic-styles');
            if (styleEl) {
                styleEl.textContent = stylesheet;
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
