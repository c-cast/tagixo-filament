<?php

namespace Ccast\TagixoFilament\Filament\Resources\Layouts\Pages;

use Ccast\Tagixo\Models\Layout;
use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\TagixoFilament\Concerns\CleansBuilderStructure;
use Ccast\TagixoFilament\Filament\Pages\FilamentVisualBuilderPage;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Filament\Actions\Action;

/**
 * Visual Builder Page for Layout Header/Footer editing
 *
 * Extends FilamentVisualBuilderPage and loads/saves
 * header or footer content from the Layout model.
 */
class BuildLayout extends FilamentVisualBuilderPage
{
    use CleansBuilderStructure;

    protected static string $resource = LayoutResource::class;

    public string $section = 'header';

    /**
     * Get the builder context
     */
    public function getContext(): string
    {
        return 'page';
    }

    /**
     * Mount the page with a record and section
     */
    public function mount(int | string $record, ?string $section = 'header'): void
    {
        $this->record = $this->resolveRecord($record);
        $this->section = in_array($section, ['header', 'footer']) ? $section : 'header';

        $this->authorizeAccess();
        $this->initializeVisualBuilder();
    }

    /**
     * Load the initial structure from the Layout model
     */
    public function loadStructure(): ?string
    {
        $contentField = "{$this->section}_content";
        $content = $this->record->$contentField;

        if (is_string($content) && ! empty($content)) {
            return $content;
        }

        if (is_array($content) && ! empty($content)) {
            return json_encode($content);
        }

        return null;
    }

    /**
     * Save the structure to the Layout model
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

        $contentField = "{$this->section}_content";
        $cssField = "{$this->section}_css";
        $htmlField = "{$this->section}_rendered_html";

        $this->record->update([
            $contentField => $decoded,
            $cssField => $fullCss ?: null,
            $htmlField => $result['html'],
        ]);
    }

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        $sectionLabel = __($this->section === 'header' ? 'Header' : 'Footer');

        return "{$this->record->name} — {$sectionLabel}";
    }

    /**
     * Get the page heading
     */
    public function getHeading(): string
    {
        return $this->getTitle();
    }

    /**
     * Build page header actions.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit_header')
                ->label(__('Header'))
                ->icon('heroicon-o-paint-brush')
                ->color($this->section === 'header' ? 'primary' : 'gray')
                ->url(fn () => LayoutResource::getUrl('build', [
                    'record' => $this->record,
                    'section' => 'header',
                ])),
            Action::make('edit_footer')
                ->label(__('Footer'))
                ->icon('heroicon-o-paint-brush')
                ->color($this->section === 'footer' ? 'primary' : 'gray')
                ->url(fn () => LayoutResource::getUrl('build', [
                    'record' => $this->record,
                    'section' => 'footer',
                ])),
            Action::make('back_to_layout')
                ->label(__('Layout Settings'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => LayoutResource::getUrl('edit', [
                    'record' => $this->record,
                ])),
        ];
    }

    /**
     * Expose layout attributes to the Visual Builder Vue frontend.
     */
    public function getPageAttributesForVue(): array
    {
        return [
            [
                'key' => 'name',
                'label' => __('Name'),
                'value' => $this->record->name,
                'type' => 'string',
            ],
            [
                'key' => 'section',
                'label' => __('Section'),
                'value' => $this->section,
                'type' => 'string',
            ],
        ];
    }
}
