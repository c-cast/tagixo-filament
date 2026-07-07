@php
    $mediaItems = $getMediaItems($getState());
    $circular = $isCircular();
    $size = $getSize();
    $shapeClass = $circular ? 'rounded-full' : 'rounded-md';
@endphp

<div class="flex items-center gap-1 flex-wrap">
    @if (!empty($mediaItems))
        @foreach ($mediaItems as $item)
            @if (($item['type'] ?? null) === 'image' || (empty($item['type']) && isset($item['url']) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)(\?|$)/i', $item['url'])))
                <img
                    src="{{ $item['thumbnail_url'] ?? $item['url'] }}"
                    alt="{{ $item['alt_text'] ?? $item['filename'] ?? '' }}"
                    style="width: {{ $size }}px; height: {{ $size }}px; object-fit: cover;"
                    class="{{ $shapeClass }} border border-gray-200"
                >
            @else
                <div
                    class="{{ $shapeClass }} border border-gray-200 flex items-center justify-center bg-gray-100"
                    style="width: {{ $size }}px; height: {{ $size }}px;"
                >
                    @if (($item['type'] ?? null) === 'video')
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:1rem;height:1rem;" viewBox="0 0 20 20" fill="currentColor" class="text-gray-500">
                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v8a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:1rem;height:1rem;" viewBox="0 0 20 20" fill="currentColor" class="text-gray-500">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586V3h4a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </div>
            @endif
        @endforeach
    @elseif ($getPlaceholder())
        <span class="text-gray-400 text-sm">{{ $getPlaceholder() }}</span>
    @endif
</div>
