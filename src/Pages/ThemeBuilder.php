<?php

namespace Tagixo\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Tagixo\Filament\Resources\LayoutResource;
use Tagixo\Filament\Resources\PageResource;
use Tagixo\Filament\TagixoFilamentPlugin;
use Tagixo\PageBuilder\Builder\LayoutFrameBuilder;
use Tagixo\PageBuilder\Facades\PageBuilder;
use Tagixo\PageBuilder\Models\Layout;
use Tagixo\PageBuilder\Services\LayoutConditionService;
use UnitEnum;

/**
 * The templates of the site, zone by zone: every layout with its header, its body
 * and its footer, each saying whether it has been built and opening the editor
 * where that zone actually lives.
 *
 * The header and the footer belong to the layout, so they open it
 * (`?type=layouts&scope=…`). A body never does: pages own their body, and a
 * template scoped to a model stands for that model's archive or single page —
 * which is created the moment its body is opened, which is why templates do not
 * have to be pre-generated.
 */
class ThemeBuilder extends Page
{
    protected static ?string $slug = 'theme-builder';

    protected static ?int $navigationSort = 15;

    protected string $view = 'tagixo-filament::pages.theme-builder';

    public static function getNavigationLabel(): string
    {
        return __('Theme Builder');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Theme Builder');
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('tagixo-filament.icons.theme-builder', 'heroicon-o-squares-2x2');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return app(TagixoFilamentPlugin::class)->getNavigationGroup();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new-template')
                ->label(__('New template'))
                ->icon('heroicon-o-plus')
                ->url(LayoutResource::getUrl('create')),
        ];
    }

    /**
     * Every template, global first: the order a theme reads in.
     *
     * @return Collection<int, Layout>
     */
    public function templates(): Collection
    {
        return LayoutResource::getModel()::query()
            ->orderByDesc('is_global')
            ->orderBy('name')
            ->get();
    }

    /**
     * The three zones of one template, as the panel shows them: what each holds,
     * whether it is there yet, and where it is edited.
     *
     * @return list<array<string, mixed>>
     */
    public function zones(Layout $layout): array
    {
        $frame = app(LayoutFrameBuilder::class)->forLayout($layout);

        return [
            $this->sectionZone($layout, $frame, 'header'),
            $this->bodyZone($layout, $frame['body']),
            $this->sectionZone($layout, $frame, 'footer'),
        ];
    }

    /**
     * @param  array<string, mixed>  $frame
     * @return array<string, mixed>
     */
    protected function sectionZone(Layout $layout, array $frame, string $section): array
    {
        return [
            'scope' => $section,
            'label' => $frame[$section]['label'],
            'configured' => (bool) $frame[$section]['available'],
            'description' => $frame[$section]['sourceDescription'],
            'url' => LayoutResource::builderUrl($layout, $section),
            'editable' => true,
        ];
    }

    /**
     * The body of a template. For a model-scoped one it is that model's page,
     * created here the first time it is opened — the lazy step the Theme Builder
     * exists for. For any other template the body belongs to each page.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected function bodyZone(Layout $layout, array $body): array
    {
        $page = null;

        if ($body['sourceKind'] === 'model' && is_string($body['sourceModel'])) {
            $page = PageBuilder::findRoutePagesForModel($body['sourceModel'])[$body['sourceTemplateType']] ?? null;
        }

        return [
            'scope' => 'body',
            'label' => $body['label'],
            'configured' => (bool) $body['available'],
            'description' => $body['sourceDescription'],
            'url' => $body['sourceKind'] === 'model'
                ? static::bodyUrl($layout)
                : ($page !== null ? PageResource::builderUrl($page) : null),
            'editable' => $body['sourceKind'] === 'model',
        ];
    }

    /**
     * Opening the body of a model template goes through a route of the SDK,
     * because the page it edits may not exist yet and has to be created on the
     * way there.
     */
    public static function bodyUrl(Layout $layout): string
    {
        return route('tagixo-filament.templates.body', ['layout' => $layout->getKey()]);
    }

    public function conditionsSummary(Layout $layout): string
    {
        $conditions = is_array($layout->conditions) ? $layout->conditions : [];

        if ($conditions === []) {
            return $layout->is_global ? __('Everything else') : __('Nothing yet');
        }

        $service = app(LayoutConditionService::class);

        return implode(', ', array_map(
            static fn (mixed $condition): string => is_array($condition) ? $service->getConditionLabel($condition) : '',
            $conditions,
        ));
    }

    public function settingsUrl(Layout $layout): string
    {
        return LayoutResource::getUrl('edit', ['record' => $layout->getKey()]);
    }
}
