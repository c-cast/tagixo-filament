<?php

namespace Ccast\TagixoFilament\MediaGallery\Livewire;

use Ccast\Tagixo\MediaGallery\Models\Media;
use Livewire\Component;

class GlobalMediaGalleryModal extends Component
{
    public bool $isOpen = false;

    public ?string $statePath = null;

    public bool $multiple = false;

    public ?int $maxFiles = null;

    public array $acceptedTypes = [];

    public $currentSelection = null;

    protected $listeners = [
        'media-selected-from-selector' => 'handleMediaSelected',
        'close-modal' => 'closeModal',
    ];

    public function mount(): void
    {
        // Will be opened via Alpine event
    }

    public function handleMediaSelected(array $mediaIds): void
    {
        // Fetch media data (ID + URL) to pass to the field
        $mediaData = Media::whereIn('id', $mediaIds)
            ->get()
            ->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->url,
                'thumbnail_url' => $media->thumbnail_url,
                'filename' => $media->filename,
            ])
            ->keyBy('id')
            ->toArray();

        // Dispatch as browser event for Alpine (used by MediaGalleryField)
        $this->js("window.dispatchEvent(new CustomEvent('media-selected', {
            detail: {
                statePath: '{$this->statePath}',
                mediaIds: ".json_encode($mediaIds).',
                mediaData: '.json_encode($mediaData).'
            }
        }))');

        // Close modal
        $this->isOpen = false;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('media-gallery::livewire.global-media-gallery-modal');
    }
}
