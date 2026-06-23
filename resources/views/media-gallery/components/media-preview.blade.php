@props(['media'])

<div class="p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Preview --}}
        <div>
            @if($media->isImage())
                <img
                    src="{{ $media->url }}"
                    alt="{{ $media->alt_text ?? $media->filename }}"
                    class="w-full rounded-lg shadow-lg"
                >
            @elseif($media->isVideo())
                <video
                    controls
                    class="w-full rounded-lg shadow-lg"
                >
                    <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                    {{ __('Your browser does not support the video tag.') }}
                </video>
            @else
                <div class="flex items-center justify-center h-64 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">{{ $media->filename }}</p>
                        <a
                            href="{{ $media->url }}"
                            target="_blank"
                            class="mt-2 inline-flex items-center text-sm text-primary-600 hover:text-primary-500"
                        >
                            {{ __('Download File') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $media->title ?: $media->filename }}
                </h3>
                @if($media->description)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ $media->description }}
                    </p>
                @endif
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Filename') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $media->filename }}</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('File Size') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $media->formatted_size }}</span>
                </div>

                @if($media->width && $media->height)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Dimensions') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $media->width }} × {{ $media->height }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Type') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $media->mime_type }}</span>
                </div>

                @if($media->folder)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Folder') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $media->folder }}</span>
                    </div>
                @endif

                @if($media->uploader)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Uploaded By') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $media->uploader->name }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Uploaded At') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $media->created_at->format('Y-m-d H:i') }}</span>
                </div>

                @if($media->alt_text)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Alt Text') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $media->alt_text }}</span>
                    </div>
                @endif
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex gap-2">
                    <a
                        href="{{ $media->url }}"
                        target="_blank"
                        download
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ __('Download') }}
                    </a>
                    <button
                        type="button"
                        onclick="navigator.clipboard.writeText('{{ $media->url }}')"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ __('Copy URL') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
