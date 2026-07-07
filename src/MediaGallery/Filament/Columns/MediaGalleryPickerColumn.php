<?php

namespace Ccast\TagixoFilament\MediaGallery\Filament\Columns;

use Closure;
use Filament\Tables\Columns\Column;

class MediaGalleryPickerColumn extends Column
{
    protected bool|Closure $isCircular = false;

    protected int|Closure $size = 48;

    protected bool|Closure $isMultiple = false;

    public function circular(bool|Closure $condition = true): static
    {
        $this->isCircular = $condition;

        return $this;
    }

    public function size(int|Closure $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isCircular(): bool
    {
        return (bool) $this->evaluate($this->isCircular);
    }

    public function getSize(): int
    {
        return (int) $this->evaluate($this->size);
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    public function getView(): string
    {
        return 'tagixo-filament::media-gallery.columns.media-gallery-picker';
    }

    public function getMediaItems(mixed $state): array
    {
        if (empty($state)) {
            return [];
        }

        if (is_array($state) && array_key_exists('url', $state)) {
            return [$state];
        }

        if (is_array($state) && ! empty($state) && is_array($state[0])) {
            return $state;
        }

        return [];
    }
}
