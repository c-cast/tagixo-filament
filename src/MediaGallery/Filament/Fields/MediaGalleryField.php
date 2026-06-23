<?php

namespace Ccast\TagixoFilament\MediaGallery\Filament\Fields;

use Filament\Forms\Components\Field;

/**
 * MediaGalleryField
 *
 * A custom Filament field for selecting media from the gallery
 * or uploading new files.
 */
class MediaGalleryField extends Field
{
    protected string $view = 'media-gallery::fields.media-gallery-field';

    /**
     * Allow multiple media selection.
     */
    protected bool $multiple = false;

    /**
     * Maximum number of files (for multiple selection).
     */
    protected ?int $maxFiles = null;

    /**
     * Allowed file types.
     */
    protected array $acceptedFileTypes = [];

    /**
     * Maximum file size in KB.
     */
    protected ?int $maxSize = null;

    /**
     * Enable crop functionality.
     */
    protected bool $enableCrop = false;

    /**
     * Crop presets.
     */
    protected array $cropPresets = [];

    /**
     * Image quality (1-100).
     */
    protected int $imageQuality = 85;

    /**
     * Allow multiple file selection.
     */
    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    /**
     * Set maximum number of files.
     */
    public function maxFiles(?int $count): static
    {
        $this->maxFiles = $count;

        return $this;
    }

    /**
     * Set accepted file types (mime types).
     */
    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    /**
     * Set maximum file size in KB.
     */
    public function maxSize(?int $sizeInKb): static
    {
        $this->maxSize = $sizeInKb;

        return $this;
    }

    /**
     * Enable crop functionality.
     */
    public function enableCrop(bool $condition = true): static
    {
        $this->enableCrop = $condition;

        return $this;
    }

    /**
     * Set crop presets.
     */
    public function cropPresets(array $presets): static
    {
        $this->cropPresets = $presets;

        return $this;
    }

    /**
     * Set image quality (1-100).
     */
    public function imageQuality(int $quality): static
    {
        $this->imageQuality = min(100, max(1, $quality));

        return $this;
    }

    /**
     * Get the field state.
     */
    public function getState(): mixed
    {
        $state = parent::getState();

        // Convert single ID to array for multiple selection
        if ($this->multiple && ! is_array($state)) {
            return $state ? [$state] : [];
        }

        return $state;
    }

    /**
     * Get whether multiple selection is enabled.
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Get the maximum number of files.
     */
    public function getMaxFiles(): ?int
    {
        return $this->maxFiles;
    }

    /**
     * Get accepted file types.
     */
    public function getAcceptedFileTypes(): array
    {
        return $this->acceptedFileTypes ?: config('tagixo.media_gallery.allowed_types', []);
    }

    /**
     * Get maximum file size.
     */
    public function getMaxSize(): ?int
    {
        return $this->maxSize ?? config('tagixo.media_gallery.max_file_size');
    }

    /**
     * Check if crop is enabled.
     */
    public function isCropEnabled(): bool
    {
        return $this->enableCrop;
    }

    /**
     * Get crop presets.
     */
    public function getCropPresets(): array
    {
        return $this->cropPresets ?: config('tagixo.media_gallery.crop_presets', []);
    }

    /**
     * Get image quality.
     */
    public function getImageQuality(): int
    {
        return $this->imageQuality;
    }
}
