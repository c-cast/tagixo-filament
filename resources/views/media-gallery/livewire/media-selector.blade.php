<div class="h-[600px] flex flex-col">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('Select Media') }}
        </h3>
    </div>

    {{-- Tabs --}}
    <div class="px-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex gap-4">
            <button
                wire:click="$set('activeTab', 'browse')"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'browse' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}"
            >
                {{ __('Browse Media') }}
            </button>
            <button
                wire:click="$set('activeTab', 'upload')"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'upload' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}"
            >
                {{ __('Upload Files') }}
            </button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div x-show="$wire.activeTab === 'browse'" class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 space-y-3" style="display: none;" x-cloak>
        {{-- Search and Filters --}}
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search media...') }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                >
            </div>

            <select
                wire:model.live="selectedType"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
            >
                <option value="">{{ __('All Types') }}</option>
                <option value="image">{{ __('Images') }}</option>
                <option value="video">{{ __('Videos') }}</option>
                <option value="document">{{ __('Documents') }}</option>
            </select>

            @if($search || $selectedType)
                <button
                    wire:click="resetFilters"
                    class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                >
                    {{ __('Reset') }}
                </button>
            @endif
        </div>

        {{-- View Mode Toggle --}}
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if(count($selected) > 0)
                    {{ __(':count selected', ['count' => count($selected)]) }}
                    <button
                        wire:click="clearSelection"
                        class="ml-2 text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        {{ __('Clear') }}
                    </button>
                @endif
            </div>

            <div class="flex gap-2">
                <button
                    wire:click="$set('viewMode', 'grid')"
                    class="p-2 {{ $viewMode === 'grid' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <button
                    wire:click="$set('viewMode', 'list')"
                    class="p-2 {{ $viewMode === 'list' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Upload Tab Content --}}
    <div x-show="$wire.activeTab === 'upload'" class="flex-1 overflow-y-auto px-6 py-4" style="display: none;" x-cloak>
        <div class="max-w-2xl mx-auto">
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <div class="mt-4">
                    <label for="file-upload" class="cursor-pointer">
                        <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('Drop files here or click to browse') }}
                        </span>
                        <input
                            id="file-upload"
                            type="file"
                            wire:model="files"
                            @if($multiple) multiple @endif
                            class="sr-only"
                        />
                    </label>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('PNG, JPG, GIF up to :size MB', ['size' => config('tagixo.media_gallery.max_file_size', 10240) / 1024]) }}
                    </p>
                </div>

                @if($files)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ __(':count file(s) selected', ['count' => count($files)]) }}
                        </p>
                        <button
                            type="button"
                            wire:click="uploadFiles"
                            wire:loading.attr="disabled"
                            class="mt-3 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="uploadFiles">{{ __('Upload') }}</span>
                            <span wire:loading wire:target="uploadFiles">{{ __('Uploading...') }}</span>
                        </button>
                    </div>
                @endif

                @error('files.*')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Media Grid/List with Detail Panel --}}
    <div x-show="$wire.activeTab === 'browse'" class="flex-1 flex overflow-hidden" style="display: none;" x-cloak>
        {{-- Left: Media Grid --}}
        <div class="flex-1 overflow-y-auto px-4 py-4">
            @if($media->count() > 0)
                @if($viewMode === 'grid')
                    <div class="flex flex-wrap gap-2">
                        @foreach($media as $item)
                            <div
                                wire:key="media-grid-{{ $item->id }}"
                                wire:click="focusMedia({{ $item->id }})"
                                wire:dblclick="toggleSelect({{ $item->id }})"
                                class="relative group cursor-pointer"
                            >
                                <div class="relative w-20 h-20 rounded-lg overflow-hidden border-2 {{ $focusedMediaId === $item->id ? 'border-blue-500 ring-2 ring-blue-500' : ($this->isSelected($item->id) ? 'border-primary-500 ring-2 ring-primary-500' : 'border-gray-200 dark:border-gray-700') }} hover:border-primary-400 transition-all" title="{{ $item->filename }}">
                                    @if($item->isImage())
                                        <img
                                            src="{{ $item->thumbnail_url }}"
                                            alt="{{ $item->alt_text ?? $item->filename }}"
                                            class="w-full h-full object-cover"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif

                                    @if($this->isSelected($item->id))
                                        <div class="absolute top-1 right-1 bg-primary-500 text-white rounded-full p-0.5">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($media as $item)
                            <div
                                wire:key="media-list-{{ $item->id }}"
                                wire:click="focusMedia({{ $item->id }})"
                                wire:dblclick="toggleSelect({{ $item->id }})"
                                class="flex items-center gap-4 p-3 rounded-lg border {{ $focusedMediaId === $item->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : ($this->isSelected($item->id) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700') }} hover:border-primary-400 cursor-pointer transition-all"
                            >
                                @if($item->isImage())
                                    <img
                                        src="{{ $item->thumbnail_url }}"
                                        alt="{{ $item->alt_text ?? $item->filename }}"
                                        class="w-12 h-12 object-cover rounded"
                                    >
                                @else
                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $item->filename }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $item->formatted_size }} • {{ $item->type }}
                                    </div>
                                </div>

                                @if($this->isSelected($item->id))
                                    <div class="text-primary-500">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $media->links() }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-center">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No media found') }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Right: Detail Panel --}}
        @if($focusedMediaId && $focusedMedia)
            <div class="w-80 border-l border-gray-200 dark:border-gray-700 overflow-y-auto px-4 py-4 bg-gray-50 dark:bg-gray-800/50">
                {{-- Preview --}}
                <div class="aspect-video bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden mb-4">
                    @if($focusedMedia->isImage())
                        <img
                            src="{{ $focusedMedia->url }}"
                            alt="{{ $focusedMedia->alt_text ?? $focusedMedia->filename }}"
                            class="w-full h-full object-contain"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Metadata Info --}}
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ $focusedMedia->formatted_size }}
                    @if($focusedMedia->width && $focusedMedia->height)
                        • {{ $focusedMedia->width }}×{{ $focusedMedia->height }} px
                    @endif
                </div>

                {{-- Variant Selector --}}
                @if(count($availableVariants) > 1)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Select Size') }}
                        </label>
                        <select
                            wire:model.live="selectedVariant"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                        >
                            @foreach($availableVariants as $key => $variant)
                                <option value="{{ $key }}">{{ $variant['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Edit Form (Filament) --}}
                <div class="mb-6">
                    {{ $this->form }}
                </div>

                {{-- Actions --}}
                <div class="space-y-2">
                    <button
                        wire:click="saveMediaDetails"
                        wire:loading.attr="disabled"
                        class="w-full px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="saveMediaDetails">{{ __('Save Details') }}</span>
                        <span wire:loading wire:target="saveMediaDetails">{{ __('Saving...') }}</span>
                    </button>

                    <button
                        wire:click="toggleSelect({{ $focusedMediaId }})"
                        class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors"
                    >
                    @if($this->isSelected($focusedMediaId))
                        {{ __('Deselect Image') }}
                    @else
                        {{ __('Select This Image') }}
                    @endif
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div>
            @if($multiple && $maxFiles)
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Max :max files', ['max' => $maxFiles]) }}
                </span>
            @endif
        </div>

        <div class="flex gap-3">
            <button
                type="button"
                wire:click="$dispatch('close-modal')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            >
                {{ __('Cancel') }}
            </button>

            @if($multiple)
                <button
                    wire:click="confirmSelection"
                    type="button"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    @disabled(!$selected)
                >
                    {{ __('Select :count', ['count' => count($selected)]) }}
                </button>
            @endif
        </div>
    </div>
</div>
