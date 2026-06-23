@php
    $mediaItems = $getMedia($getState());
    $size = $getSize() ?? 60;
    $circular = $isCircular();
    $lightbox = $hasLightbox();
    $showTitle = $shouldShowTitle();
@endphp

<div class="flex items-center gap-2">
    @forelse($mediaItems as $media)
        <div
            class="relative group"
            @if($showTitle && $media->title)
                title="{{ $media->title }}"
            @endif
        >
            @if($media->isImage())
                @if($lightbox)
                    <a
                        href="{{ $media->url }}"
                        target="_blank"
                        class="block"
                    >
                        <img
                            src="{{ $media->thumbnail_url }}"
                            alt="{{ $media->alt_text ?? $media->filename }}"
                            class="object-cover {{ $circular ? 'rounded-full' : 'rounded-lg' }} border-2 border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-400 transition-colors"
                            style="width: {{ $size }}px; height: {{ $size }}px;"
                        >
                    </a>
                @else
                    <img
                        src="{{ $media->thumbnail_url }}"
                        alt="{{ $media->alt_text ?? $media->filename }}"
                        class="object-cover {{ $circular ? 'rounded-full' : 'rounded-lg' }} border-2 border-gray-200 dark:border-gray-700"
                        style="width: {{ $size }}px; height: {{ $size }}px;"
                    >
                @endif
            @else
                {{-- Non-image media: show icon --}}
                <div
                    class="flex items-center justify-center bg-gray-100 dark:bg-gray-700 {{ $circular ? 'rounded-full' : 'rounded-lg' }} border-2 border-gray-200 dark:border-gray-700"
                    style="width: {{ $size }}px; height: {{ $size }}px;"
                >
                    @if($media->isVideo())
                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($media->isDocument())
                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    @endif
                </div>

                @if($lightbox)
                    <a
                        href="{{ $media->url }}"
                        target="_blank"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline mt-1 block truncate"
                        style="max-width: {{ $size }}px;"
                    >
                        {{ $media->filename }}
                    </a>
                @endif
            @endif
        </div>
    @empty
        <div class="text-gray-400 dark:text-gray-600 text-sm">
            {{ __('No media') }}
        </div>
    @endforelse
</div>
