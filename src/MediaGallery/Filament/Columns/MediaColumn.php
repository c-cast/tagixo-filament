<?php

namespace Ccast\TagixoFilament\MediaGallery\Filament\Columns;

use Ccast\Tagixo\MediaGallery\Models\Media;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Collection;

/**
 * MediaColumn
 *
 * A custom Filament table column for displaying media thumbnails.
 */
class MediaColumn extends Column
{
    protected string $view = 'media-gallery::columns.media-column';

    /**
     * Render thumbnails as circles instead of rounded squares.
     */
    protected bool $circular = false;

    /**
     * Enable lightbox on click.
     */
    protected bool $lightbox = true;

    /**
     * Show media title as tooltip.
     */
    protected bool $showTitle = true;

    /**
     * Default thumbnail size.
     */
    protected string | int | null $size = 60;

    /**
     * Enable or disable circular thumbnails.
     */
    public function circular(bool $condition = true): static
    {
        $this->circular = $condition;

        return $this;
    }

    /**
     * Check if thumbnails are circular.
     */
    public function isCircular(): bool
    {
        return $this->circular;
    }

    /**
     * Set the thumbnail size in pixels.
     */
    public function size(string | int | null $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the thumbnail size.
     */
    public function getSize(): string | int | null
    {
        return $this->size;
    }

    /**
     * Enable or disable lightbox.
     */
    public function lightbox(bool $condition = true): static
    {
        $this->lightbox = $condition;

        return $this;
    }

    /**
     * Enable or disable title tooltip.
     */
    public function showTitle(bool $condition = true): static
    {
        $this->showTitle = $condition;

        return $this;
    }

    /**
     * Check if lightbox is enabled.
     */
    public function hasLightbox(): bool
    {
        return $this->lightbox;
    }

    /**
     * Check if title should be shown.
     */
    public function shouldShowTitle(): bool
    {
        return $this->showTitle;
    }

    /**
     * Get the media record(s) from the state.
     */
    public function getMedia($state): array
    {
        if (is_null($state)) {
            return [];
        }

        // Handle single ID
        if (is_numeric($state)) {
            $media = Media::find($state);

            return $media ? [$media] : [];
        }

        // Handle array of IDs
        if (is_array($state)) {
            return Media::whereIn('id', $state)->get()->all();
        }

        // Handle collection
        if ($state instanceof Collection) {
            return $state->all();
        }

        return [];
    }
}
