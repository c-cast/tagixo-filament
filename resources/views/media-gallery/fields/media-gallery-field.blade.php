@php
    $statePath = $getStatePath();
    $isMultiple = $isMultiple();
    $maxFiles = $getMaxFiles();
    $selectedIds = $getState() ?: ($isMultiple ? [] : null);

    // Get initial media URL if state exists (for page load with existing value)
    $initialMediaUrl = null;
    if (!$isMultiple && $selectedIds) {
        $media = \Ccast\Tagixo\MediaGallery\Models\Media::find($selectedIds);
        $initialMediaUrl = $media?->thumbnail_url ?? $media?->url;
    }
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: @entangle($statePath).live ?? @js($isMultiple ? [] : null),
            statePath: @js($statePath),
            isMultiple: @js($isMultiple),
            maxFiles: @js($maxFiles),
            acceptedTypes: @js($getAcceptedFileTypes()),
            mediaUrl: @js($initialMediaUrl),
            mediaData: {},

            init() {
                // Listen for media selection from the global modal
                window.addEventListener('media-selected', (event) => {
                    // Only handle if this is for our field
                    if (event.detail.statePath === this.statePath) {
                        this.handleMediaSelection(event.detail.mediaIds, event.detail.mediaData || {});
                    }
                });
            },

            handleMediaSelection(mediaIds, mediaData) {
                this.mediaData = mediaData;

                if (this.isMultiple) {
                    this.state = Array.isArray(mediaIds) ? mediaIds : [mediaIds];
                } else {
                    const id = Array.isArray(mediaIds) ? mediaIds[0] : mediaIds;
                    this.state = id;
                    // Set the URL from the provided mediaData
                    if (mediaData[id]) {
                        this.mediaUrl = mediaData[id].thumbnail_url || mediaData[id].url;
                    }
                }
            },

            openGallery() {
                // Dispatch event to global modal
                window.dispatchEvent(new CustomEvent('open-media-gallery', {
                    detail: {
                        statePath: this.statePath,
                        multiple: this.isMultiple,
                        maxFiles: this.maxFiles,
                        acceptedTypes: this.acceptedTypes,
                        currentSelection: this.state
                    }
                }));
            },

            removeMedia() {
                if (this.isMultiple) {
                    this.state = [];
                } else {
                    this.state = null;
                    this.mediaUrl = null;
                }
            }
        }"
        class="space-y-2"
    >
        <!-- Media Selector Div -->
        <div class="relative">
            <div
                @click="openGallery()"
                class="w-full aspect-video max-w-sm border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors cursor-pointer bg-white dark:bg-gray-800 overflow-hidden"
                :class="{ 'border-primary-500 dark:border-primary-400': state && !isMultiple }"
            >
                <!-- Empty State - Icon -->
                <div x-show="!state || (isMultiple && (state?.length || 0) === 0)" class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Click to select media') }}</p>
                    </div>
                </div>

                <!-- Selected Media - Image Preview (Single Mode) -->
                <div x-show="state && !isMultiple" class="absolute inset-0">
                    <img x-show="mediaUrl" :src="mediaUrl" alt="Selected media" class="w-full h-full object-cover" />
                    <div x-show="!mediaUrl && state" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                        <svg class="w-12 h-12 text-gray-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Selected Media - Multiple Mode -->
                <div x-show="state && isMultiple && (state?.length || 0) > 0" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white" x-text="(state?.length || 0) + ' media item(s)'"></p>
                    </div>
                </div>
            </div>

            <!-- Remove Button (Overlay) -->
            <button
                x-show="state && (isMultiple ? (state?.length || 0) > 0 : true)"
                @click.stop="removeMedia()"
                type="button"
                class="absolute top-2 right-2 p-2 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg transition-colors"
                title="{{ __('Remove') }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</x-dynamic-component>
