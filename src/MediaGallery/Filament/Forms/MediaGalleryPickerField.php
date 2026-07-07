<?php

namespace Ccast\TagixoFilament\MediaGallery\Filament\Forms;

use Closure;
use Filament\Forms\Components\Field;

class MediaGalleryPickerField extends Field
{
    protected string $view = 'tagixo-filament::media-gallery.forms.media-gallery-picker';

    protected bool|Closure $isMultiple = false;

    protected int|Closure|null $maxFiles = null;

    protected array|Closure $acceptedTypes = [];

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function maxFiles(int|Closure|null $count): static
    {
        $this->maxFiles = $count;

        return $this;
    }

    public function acceptedTypes(array|Closure $types): static
    {
        $this->acceptedTypes = $types;

        return $this;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    public function getMaxFiles(): ?int
    {
        return $this->evaluate($this->maxFiles);
    }

    public function getAcceptedTypes(): array
    {
        return $this->evaluate($this->acceptedTypes);
    }
}
