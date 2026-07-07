<?php

namespace Ccast\TagixoFilament\Filament\Resources\Menus\Forms;

use Ccast\Tagixo\Enums\MenuItemTargetType;
use Ccast\Tagixo\Models\Page;
use Ccast\Tagixo\Support\MenuTreeStructure;
use Filament\Forms\Components\Field;

/**
 * WordPress-style menu builder field for Filament v5.
 *
 * Renders a flat, depth-aware tree the user reorders by drag (Filament's native
 * Alpine `x-sortable`) and re-levels with indent/outdent, editing each item in a
 * modal. All state manipulation happens client-side in Alpine against the
 * `@entangle`d state; the Create/Edit pages convert flat↔nested at persist time
 * ({@see MenuTreeStructure}). Filament/Livewire analog of the Primix LiVue field.
 */
class MenuTreeField extends Field
{
    protected string $view = 'tagixo-filament::filament.resources.menus.menu-tree';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
    }

    /**
     * Always hand the view an array, never null.
     */
    public function getState(): mixed
    {
        $state = parent::getState();

        return is_array($state) ? array_values($state) : [];
    }

    /**
     * A blank item for the "Add item" button.
     *
     * @return array<string, mixed>
     */
    public function getBlankItem(): array
    {
        return MenuTreeStructure::blankItem();
    }

    /**
     * Link-type options in the {value, label} shape the <select> uses.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function getLinkTypeOptions(): array
    {
        $options = [];

        foreach (MenuItemTargetType::options() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    /**
     * Page picker options: page id => "Title (slug)".
     *
     * Mirrors the folding done by {@see PersistsMenuItems}.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function getPageOptions(): array
    {
        return Page::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug'])
            ->map(fn (Page $page) => [
                'value' => $page->id,
                'label' => trim(($page->title ?: __('Untitled')).' ('.$page->slug.')'),
            ])
            ->all();
    }
}
