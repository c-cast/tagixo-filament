<?php

namespace Ccast\TagixoFilament\Filament\Resources\Popups\Pages;

use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\TagixoFilament\Concerns\CleansBuilderStructure;
use Ccast\TagixoFilament\Filament\Pages\FilamentVisualBuilderPage;
use Ccast\TagixoFilament\Filament\Resources\Popups\PopupResource;

/**
 * Visual Builder page for Popups.
 *
 * Mirrors BuildPage but runs in the `popup` context and renders the saved
 * structure through PageRenderer::renderPopupContent(), which wraps the
 * components in a `.vb-body` scoped to this popup's id. Global-variable CSS is
 * prepended for parity with the page builder.
 */
class BuildPopup extends FilamentVisualBuilderPage
{
    use CleansBuilderStructure;

    protected static string $resource = PopupResource::class;

    public function getContext(): string
    {
        return 'popup';
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

        $renderer = app(PageRenderer::class);
        $result = $renderer->renderPopupContent($decoded, 'tgx-popup-'.$this->record->id);

        $globalVarsCss = $renderer->generateGlobalVariablesCss();
        $componentCss = $result['css'] ?? '';
        $fullCss = ($globalVarsCss ? $globalVarsCss."\n" : '').$componentCss;

        $this->record->update([
            'content' => $decoded,
            'rendered_html' => $result['html'] ?? '',
            'css' => $fullCss !== '' ? $fullCss : null,
        ]);
    }

    public function getTitle(): string
    {
        return __('Visual Builder').': '.$this->record->title;
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
            ['key' => 'title', 'label' => __('Title'), 'value' => $record->title, 'type' => 'string'],
            ['key' => 'slug', 'label' => __('Slug'), 'value' => $record->slug, 'type' => 'string'],
            ['key' => 'status', 'label' => __('Status'), 'value' => $record->status, 'type' => 'string'],
        ];
    }
}
