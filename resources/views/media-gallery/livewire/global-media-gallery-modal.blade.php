<div
    x-data="{
        isOpen: @entangle('isOpen'),
        statePath: @entangle('statePath'),
        multiple: @entangle('multiple'),
        maxFiles: @entangle('maxFiles'),
        acceptedTypes: @entangle('acceptedTypes'),
        currentSelection: @entangle('currentSelection'),

        init() {
            // Listen for open requests from fields
            window.addEventListener('open-media-gallery', (event) => {
                this.statePath = event.detail.statePath;
                this.multiple = event.detail.multiple;
                this.maxFiles = event.detail.maxFiles;
                this.acceptedTypes = event.detail.acceptedTypes;
                this.currentSelection = event.detail.currentSelection;
                this.isOpen = true;
            });
        }
    }"
    x-show="isOpen"
    x-cloak
    @keydown.escape.window="$wire.closeModal()"
    class="fixed inset-0 z-[9999] overflow-y-auto"
    style="display: none;"
    @click.self="$wire.closeModal()"
>
    <!-- Background overlay -->
    <div
        x-show="isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9998] transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 pointer-events-none"
    ></div>

    <div class="relative z-[9999] flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Modal panel -->
        <div
            x-show="isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @click.stop
            class="inline-block w-full max-w-6xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg"
        >
            <livewire:media-gallery::media-selector
                wire:key="global-media-selector"
                :multiple="$multiple"
                :max-files="$maxFiles"
                :accepted-types="$acceptedTypes"
            />
        </div>
    </div>
</div>
