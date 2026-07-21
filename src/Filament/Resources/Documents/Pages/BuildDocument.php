<?php

namespace Ccast\TagixoFilament\Filament\Resources\Documents\Pages;

use Ccast\Tagixo\Renderers\DocumentRenderer;
use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\TagixoFilament\Filament\Pages\FilamentVisualBuilderPage;
use Ccast\TagixoFilament\Filament\Resources\Documents\DocumentResource;

/**
 * Visual Builder page for documents.
 *
 * Mirrors BuildPage but runs in the `document` context and renders the saved
 * structure through DocumentRenderer, which wraps the components in a print-ready
 * HTML scaffold sized to the record's paper/orientation/margin. Global-variable
 * CSS is passed as extra CSS so documents honour the same variables as pages.
 */
class BuildDocument extends FilamentVisualBuilderPage
{

    protected static string $resource = DocumentResource::class;

    public function getContext(): string
    {
        return 'document';
    }

    protected function authorizeAccess(): void
    {
        // No specific authorization yet — add permission checks here if needed.
    }

    public function loadStructure(): ?string
    {
        $content = $this->record->content;

        if (is_string($content) && ! empty($content)) {
            return $content;
        }

        if (is_array($content) && ! empty($content)) {
            return json_encode($content);
        }

        return null;
    }

    public function saveStructure(string $structure): void
    {
        $decoded = json_decode($structure, true);
        $decoded = $this->cleanStructure($decoded);

        $globalVarsCss = app(PageRenderer::class)->generateGlobalVariablesCss();

        $html = app(DocumentRenderer::class)->renderFromJson(
            $decoded,
            $globalVarsCss,
            (string) $this->record->name,
            (string) ($this->record->paper_size ?: 'A4'),
            (string) ($this->record->orientation ?: 'portrait'),
            (string) ($this->record->margin ?: '2cm'),
        );

        $this->record->update([
            'content' => $decoded,
            'rendered_html' => $html,
        ]);
    }

    public function getTitle(): string
    {
        return __('Visual Builder').': '.$this->record->name;
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    /**
     * @return array<int, array{key: string, label: string, value: mixed, type: string}>
     */
    public function getPageAttributesForVue(): array
    {
        $record = $this->record;

        return [
            ['key' => 'name', 'label' => __('Name'), 'value' => $record->name, 'type' => 'string'],
            ['key' => 'slug', 'label' => __('Slug'), 'value' => $record->slug, 'type' => 'string'],
            ['key' => 'paper_size', 'label' => __('Paper size'), 'value' => $record->paper_size, 'type' => 'string'],
            ['key' => 'orientation', 'label' => __('Orientation'), 'value' => $record->orientation, 'type' => 'string'],
            ['key' => 'status', 'label' => __('Status'), 'value' => $record->status?->value, 'type' => 'string'],
        ];
    }
}
