<?php

namespace Ccast\TagixoFilament\MediaGallery\Http\Livewire;

use Ccast\Tagixo\MediaGallery\Exceptions\MediaUploadException;
use Ccast\Tagixo\MediaGallery\Models\Media;
use Ccast\Tagixo\MediaGallery\Services\MediaService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Media Selector Component
 *
 * Livewire component for selecting media from gallery or uploading new files.
 */
class MediaSelector extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use WithFileUploads;
    use WithPagination;

    /**
     * Allow multiple selection.
     */
    public bool $multiple = false;

    /**
     * Maximum number of files.
     */
    public ?int $maxFiles = null;

    /**
     * Accepted file types.
     */
    public array $acceptedTypes = [];

    /**
     * Search query.
     */
    public string $search = '';

    /**
     * Selected folder filter.
     */
    public ?string $selectedFolder = null;

    /**
     * Selected type filter.
     */
    public ?string $selectedType = null;

    /**
     * Selected media IDs.
     */
    public array $selected = [];

    /**
     * Files to upload.
     */
    public $files = [];

    /**
     * View mode (grid or list).
     */
    public string $viewMode = 'grid';

    /**
     * Active tab (browse or upload).
     */
    public string $activeTab = 'browse';

    /**
     * Currently focused media for detail panel.
     */
    public ?int $focusedMediaId = null;

    /**
     * Form data for editing media details.
     */
    public ?array $data = [];

    /**
     * Selected variant/size for the focused media.
     */
    public string $selectedVariant = 'original';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->multiple = $this->multiple ?? false;
        $this->maxFiles = $this->maxFiles ?? null;
        $this->acceptedTypes = $this->acceptedTypes ?? [];
        $this->form->fill();
    }

    /**
     * Define the Filament form for editing media details.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('filename')
                    ->label(__('Filename'))
                    ->placeholder(__('filename.jpg'))
                    ->required(),
                TextInput::make('title')
                    ->label(__('Title'))
                    ->placeholder(__('Image title...')),
                TextInput::make('alt_text')
                    ->label(__('Alt Text'))
                    ->placeholder(__('Alternative text for accessibility...')),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->placeholder(__('Optional description...'))
                    ->rows(2),
            ])
            ->statePath('data');
    }

    /**
     * Get media query.
     */
    public function getMediaQuery()
    {
        $query = Media::query()->originals()->latest();

        // Apply search
        if (! empty($this->search)) {
            $query->search($this->search);
        }

        // Apply folder filter
        if ($this->selectedFolder !== null) {
            $query->inFolder($this->selectedFolder);
        }

        // Apply type filter
        if ($this->selectedType) {
            match ($this->selectedType) {
                'image' => $query->images(),
                'video' => $query->videos(),
                'document' => $query->documents(),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Get media items.
     */
    public function getMediaProperty(): LengthAwarePaginator
    {
        return $this->getMediaQuery()->paginate(24);
    }

    /**
     * Get available folders.
     */
    public function getFoldersProperty(): Collection
    {
        return Media::query()
            ->originals()
            ->select('folder')
            ->whereNotNull('folder')
            ->distinct()
            ->pluck('folder');
    }

    /**
     * Toggle media selection.
     */
    public function toggleSelect(int $mediaId): void
    {
        if ($this->multiple) {
            if (in_array($mediaId, $this->selected)) {
                $this->selected = array_values(array_diff($this->selected, [$mediaId]));
            } else {
                if ($this->maxFiles && count($this->selected) >= $this->maxFiles) {
                    $this->dispatch('max-files-reached');

                    return;
                }
                $this->selected[] = $mediaId;
            }
        } else {
            $this->selected = [$mediaId];
            // Dispatch to parent modal component (GlobalMediaGalleryModal)
            $this->dispatch('media-selected-from-selector', mediaIds: [$mediaId]);
        }
    }

    /**
     * Check if media is selected.
     */
    public function isSelected(int $mediaId): bool
    {
        return in_array($mediaId, $this->selected);
    }

    /**
     * Clear selection.
     */
    public function clearSelection(): void
    {
        $this->selected = [];
    }

    /**
     * Confirm selection.
     */
    public function confirmSelection(): void
    {
        // Dispatch to parent modal component (GlobalMediaGalleryModal)
        $this->dispatch('media-selected-from-selector', mediaIds: $this->selected);
    }

    /**
     * Upload files.
     */
    public function uploadFiles(): void
    {
        $this->validate([
            'files.*' => [
                'required',
                'file',
                'max:'.config('tagixo.media_gallery.max_file_size', 10240),
            ],
        ]);

        $mediaService = app(MediaService::class);
        $uploaded = [];

        foreach ($this->files as $file) {
            try {
                // upload() runs the authoritative server-side guard and throws
                // when a file is rejected (size / MIME / executable extension).
                // Surface that as a validation error instead of a 500.
                $media = $mediaService->upload($file);
            } catch (MediaUploadException $e) {
                $this->addError('files', $e->getMessage());

                continue;
            }

            $uploaded[] = $media->id;
        }

        if ($uploaded === []) {
            return;
        }

        $this->files = [];
        $this->selected = $uploaded;
        $this->activeTab = 'browse'; // Switch to browse tab to see uploaded files

        if (! $this->multiple && count($uploaded) === 1) {
            // Dispatch to parent modal component (GlobalMediaGalleryModal)
            $this->dispatch('media-selected-from-selector', mediaIds: $uploaded);
        }

        $this->dispatch('files-uploaded', count($uploaded));
    }

    /**
     * Reset filters.
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedFolder = null;
        $this->selectedType = null;
        $this->resetPage();
    }

    /**
     * Focus on a media item to show details.
     */
    public function focusMedia(int $mediaId): void
    {
        $this->focusedMediaId = $mediaId;
        $this->selectedVariant = 'original';

        $media = Media::find($mediaId);
        if ($media) {
            $this->form->fill([
                'filename' => $media->filename ?? '',
                'title' => $media->title ?? '',
                'alt_text' => $media->alt_text ?? '',
                'description' => $media->description ?? '',
            ]);
        }
    }

    /**
     * Get the currently focused media.
     */
    public function getFocusedMediaProperty(): ?Media
    {
        if (! $this->focusedMediaId) {
            return null;
        }

        return Media::with('crops')->find($this->focusedMediaId);
    }

    /**
     * Get available variants for the focused media.
     */
    public function getAvailableVariantsProperty(): array
    {
        $media = $this->focusedMedia;
        if (! $media) {
            return [];
        }

        $variants = [
            'original' => [
                'label' => __('Original')." ({$media->width}x{$media->height})",
                'url' => $media->url,
                'width' => $media->width,
                'height' => $media->height,
            ],
        ];

        // Add thumbnail
        if ($media->getThumbnailPath()) {
            $thumbConfig = config('tagixo.media_gallery.thumbnail', ['width' => 300, 'height' => 300]);
            $variants['thumbnail'] = [
                'label' => __('Thumbnail')." ({$thumbConfig['width']}x{$thumbConfig['height']})",
                'url' => $media->thumbnail_url,
                'width' => $thumbConfig['width'],
                'height' => $thumbConfig['height'],
            ];
        }

        // Add crops
        foreach ($media->crops as $crop) {
            $key = "crop_{$crop->id}";
            $variants[$key] = [
                'label' => __('Crop')." ({$crop->width}x{$crop->height})",
                'url' => $crop->url,
                'width' => $crop->width,
                'height' => $crop->height,
                'media_id' => $crop->id,
            ];
        }

        return $variants;
    }

    /**
     * Save media details.
     */
    public function saveMediaDetails(): void
    {
        if (! $this->focusedMediaId) {
            return;
        }

        $data = $this->form->getState();

        $media = Media::find($this->focusedMediaId);
        if ($media) {
            $media->update([
                'filename' => $data['filename'],
                'title' => $data['title'],
                'alt_text' => $data['alt_text'],
                'description' => $data['description'],
            ]);

            $this->dispatch('media-details-saved');
        }
    }

    /**
     * Get the URL for the selected variant.
     */
    public function getSelectedVariantUrlProperty(): ?string
    {
        $variants = $this->availableVariants;

        return $variants[$this->selectedVariant]['url'] ?? null;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('media-gallery::livewire.media-selector', [
            'media' => $this->media,
            'folders' => $this->folders,
            'focusedMedia' => $this->focusedMedia,
            'availableVariants' => $this->availableVariants,
        ]);
    }
}
