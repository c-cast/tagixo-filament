<?php

namespace Ccast\TagixoFilament\Filament\Resources\Layouts\Pages;

use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\TagixoFilament\Concerns\CleansBuilderStructure;
use Ccast\TagixoFilament\Filament\Pages\FilamentVisualBuilderPage;
use Ccast\TagixoFilament\Filament\Pages\ThemeBuilderPage;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Filament\Actions\Action;

class BuildLayout extends FilamentVisualBuilderPage
{
    use CleansBuilderStructure;

    protected static string $resource = LayoutResource::class;

    public string $section = 'header';

    public function getContext(): string
    {
        return 'page';
    }

    public function mount(int | string $record, ?string $section = 'header'): void
    {
        $this->record = $this->resolveRecord($record);
        $this->section = in_array($section, ['header', 'body', 'footer']) ? $section : 'header';

        $this->authorizeAccess();
        $this->initializeVisualBuilder();
    }

    public function getLayoutFrameForVue(): array
    {
        $sectionLabel = __(['header' => 'Header', 'body' => 'Body', 'footer' => 'Footer'][$this->section] ?? 'Body');

        return [
            'enabled'     => true,
            'activeScope' => 'body',
            'body'   => ['scope' => 'body',   'label' => $sectionLabel, 'available' => true,  'editable' => true,  'previewHtml' => '', 'previewCss' => '', 'structure' => null],
            'header' => ['scope' => 'header', 'label' => __('Header'),  'available' => false, 'editable' => false, 'previewHtml' => '', 'previewCss' => '', 'structure' => null],
            'footer' => ['scope' => 'footer', 'label' => __('Footer'),  'available' => false, 'editable' => false, 'previewHtml' => '', 'previewCss' => '', 'structure' => null],
        ];
    }

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

    public function getTitle(): string
    {
        $sectionLabel = __(['header' => 'Header', 'body' => 'Body', 'footer' => 'Footer'][$this->section] ?? 'Header');

        return "{$this->record->name} — {$sectionLabel}";
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    public function getBackUrl(): ?string
    {
        return ThemeBuilderPage::getUrl();
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('edit_header')
                ->label(__('Header'))
                ->icon('heroicon-o-paint-brush')
                ->color($this->section === 'header' ? 'primary' : 'gray')
                ->url(fn () => LayoutResource::getUrl('build', [
                    'record' => $this->record,
                    'section' => 'header',
                ])),
        ];

        if (! $this->record->is_global) {
            $actions[] = Action::make('edit_body')
                ->label(__('Body'))
                ->icon('heroicon-o-paint-brush')
                ->color($this->section === 'body' ? 'primary' : 'gray')
                ->url(fn () => LayoutResource::getUrl('build', [
                    'record' => $this->record,
                    'section' => 'body',
                ]));
        }

        $actions[] = Action::make('edit_footer')
            ->label(__('Footer'))
            ->icon('heroicon-o-paint-brush')
            ->color($this->section === 'footer' ? 'primary' : 'gray')
            ->url(fn () => LayoutResource::getUrl('build', [
                'record' => $this->record,
                'section' => 'footer',
            ]));

        $actions[] = Action::make('back_to_layout')
            ->label(__('Theme Builder'))
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(fn () => ThemeBuilderPage::getUrl());

        return $actions;
    }

    public function getPageAttributesForVue(): array
    {
        return [
            [
                'key'   => 'name',
                'label' => __('Name'),
                'value' => $this->record->name,
                'type'  => 'string',
            ],
            [
                'key'   => 'section',
                'label' => __('Section'),
                'value' => $this->section,
                'type'  => 'string',
            ],
        ];
    }
}
