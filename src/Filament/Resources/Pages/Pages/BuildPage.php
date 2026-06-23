<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Pages;

use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\TagixoFilament\Concerns\CleansBuilderStructure;
use Ccast\TagixoFilament\Filament\Pages\FilamentVisualBuilderPage;
use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;

/**
 * Visual Builder Page for Visual Builder Pages
 *
 * This class extends FilamentVisualBuilderPage and provides
 * the page attributes to the Vue.js frontend with translated labels.
 */
class BuildPage extends FilamentVisualBuilderPage
{
    use CleansBuilderStructure;

    protected static string $resource = PageResource::class;

    /**
     * Get the builder context
     */
    public function getContext(): string
    {
        return 'page';
    }

    /**
     * Authorize access to the page
     * Override to add custom authorization
     */
    protected function authorizeAccess(): void
    {
        // For now, no specific authorization
        // Add checks here if needed (e.g., permissions)
    }

    /**
     * Load the initial structure from the Page model
     */
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

    /**
     * Save the structure to the Page model
     */
    public function saveStructure(string $structure): void
    {
        $decoded = json_decode($structure, true);
        $decoded = $this->cleanStructure($decoded);

        $renderer = app(PageRenderer::class);
        $result = $renderer->renderFromJson($decoded);

        $globalVarsCss = $renderer->generateGlobalVariablesCss();
        $componentCss = $result['css'];
        $fullCss = ($globalVarsCss ? $globalVarsCss."\n" : '').$componentCss;

        $this->record->update([
            'content' => $decoded,
            'rendered_html' => $result['html'],
            'css' => $fullCss ?: null,
        ]);
    }

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return __('Visual Builder').': '.$this->record->title;
    }

    /**
     * Get the page heading
     */
    public function getHeading(): string
    {
        return $this->getTitle();
    }

    /**
     * Expose page attributes to the Visual Builder Vue frontend.
     *
     * Each attribute has a translated label so the user sees "Titolo" instead of "title".
     * This allows modules in the Vue builder to access record attributes with proper labels.
     *
     * @return array Array of ['key' => string, 'label' => string, 'value' => mixed, 'type' => string]
     */
    public function getPageAttributesForVue(): array
    {
        $record = $this->record;
        $effectiveLayout = $record->getEffectiveLayout();

        return [
            [
                'key' => 'title',
                'label' => __('Title'),
                'value' => $record->title,
                'type' => 'string',
            ],
            [
                'key' => 'slug',
                'label' => __('Slug'),
                'value' => $record->slug,
                'type' => 'string',
            ],
            [
                'key' => 'excerpt',
                'label' => __('Excerpt'),
                'value' => $record->excerpt,
                'type' => 'text',
            ],
            [
                'key' => 'meta_title',
                'label' => __('Meta Title'),
                'value' => $record->meta_title,
                'type' => 'string',
            ],
            [
                'key' => 'meta_description',
                'label' => __('Meta Description'),
                'value' => $record->meta_description,
                'type' => 'text',
            ],
            [
                'key' => 'status',
                'label' => __('Status'),
                'value' => $record->status?->value,
                'type' => 'string',
            ],
            [
                'key' => 'template',
                'label' => __('Template'),
                'value' => $record->template,
                'type' => 'string',
            ],
            [
                'key' => 'theme',
                'label' => __('Theme'),
                'value' => $record->theme,
                'type' => 'string',
            ],
            [
                'key' => 'layout_id',
                'label' => __('Layout ID'),
                'value' => $record->layout_id,
                'type' => 'number',
            ],
            [
                'key' => 'layout_name',
                'label' => __('Layout'),
                'value' => $effectiveLayout?->name,
                'type' => 'string',
            ],
            [
                'key' => 'url',
                'label' => __('URL'),
                'value' => $record->url,
                'type' => 'string',
            ],
        ];
    }
}
