<?php

namespace Tagixo\Filament\Resources\Menus\Forms;

use Filament\Forms\Components\Field;
use Tagixo\Core\IconManager;
use Tagixo\PageBuilder\Enums\MenuItemTargetType;
use Tagixo\PageBuilder\Models\Page;
use Tagixo\PageBuilder\Support\MenuTreeStructure;

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

    /**
     * Dropdown options in the {value, label} shape the <select> uses.
     *
     * Only a top-level item set to `mega` means anything to the renderer; every
     * other item is an ordinary dropdown, which is why the normal case is the
     * empty value and not a name of its own.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function getDropdownTypeOptions(): array
    {
        return [
            ['value' => '', 'label' => __('Normal')],
            ['value' => 'mega', 'label' => __('Mega menu')],
        ];
    }

    /**
     * The icon sets the core offers, names only: the picker asks for an icon's
     * markup when it has to draw it.
     *
     * @return array<int, array{name: string, label: string, variants: array, icons: array<int, string>}>
     */
    public function getIconSets(): array
    {
        return app(IconManager::class)->getIconSetsForVue();
    }
}
