<?php

namespace Ccast\TagixoFilament\Filament\Pages;

use Ccast\Tagixo\Models\Layout;
use Ccast\Tagixo\Services\LayoutConditionService;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Validator;

use function Filament\get_authorization_response;

class ThemeBuilderPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 15;

    protected string $view = 'tagixo-filament::filament.pages.theme-builder';

    public ?int $editingLayoutId = null;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('Visual Builder');
    }

    public static function getNavigationLabel(): string
    {
        return __('Theme Builder');
    }

    public function getTitle(): string
    {
        return __('Theme Builder');
    }

    public static function canAccess(): bool
    {
        // The Theme Builder manages layouts: mirror the resources' semantics
        // by following the Layout policy (config 'tagixo-filament.policies')
        // through Filament's own authorization helper — no policy, or policy
        // without viewAny(), keeps the page open to any panel user.
        return get_authorization_response('viewAny', Layout::class)->allowed();
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'theme-builder';
    }

    public function getLayouts(): \Illuminate\Support\Collection
    {
        return Layout::orderByDesc('is_global')->orderBy('name')->get();
    }

    public function getRegisteredModels(): array
    {
        return Tagixo::getRegisteredModels();
    }

    public function openEditModal(int $layoutId): array
    {
        $layout = Layout::findOrFail($layoutId);

        $this->editingLayoutId = $layoutId;

        return [
            'name'       => $layout->name,
            'conditions' => $layout->conditions ?? [],
        ];
    }

    public function saveModal(string $name, array $conditions = []): void
    {
        Validator::make(['name' => $name], ['name' => 'required|string|max:255'])->validate();

        if ($this->editingLayoutId) {
            Layout::findOrFail($this->editingLayoutId)->update([
                'name'       => $name,
                'conditions' => $conditions ?: null,
            ]);
        } else {
            Layout::create([
                'name'       => $name,
                'conditions' => $conditions ?: null,
            ]);
        }

        $this->editingLayoutId = null;
        $this->dispatch('close-modal', id: 'layout-modal');
    }

    public function deleteLayout(int $layoutId): void
    {
        Layout::findOrFail($layoutId)->delete();
    }

    public function getBuildUrl(int $layoutId, string $section): string
    {
        // Body of a template scoped to a registered model's pages: the special
        // page (archive or single) is created lazily right here, the first
        // time the Body is opened, and the builder edits THAT page — layouts
        // and model template pages stay linked without pre-creating anything.
        if ($section === 'body') {
            $layout = Layout::find($layoutId);
            $target = $this->resolveModelPageTarget($layout?->conditions ?? []);

            if ($target !== null) {
                [$modelKey, $templateType] = $target;
                $pages = \Ccast\Tagixo\Facades\Tagixo::ensureRoutePagesForModel($modelKey);
                $page = $pages[$templateType] ?? null;

                if ($page !== null) {
                    return \Ccast\TagixoFilament\Filament\Resources\Pages\PageResource::getUrl('build', ['record' => $page]);
                }
            }
        }

        return LayoutResource::getUrl('build', ['record' => $layoutId, 'section' => $section]);
    }

    /**
     * Whether the template's Body is configured: for model-scoped templates
     * that means the special page exists and has content; otherwise the
     * layout's own baked body decides (unchanged behavior).
     */
    public function isBodyConfigured($layout): bool
    {
        $target = $this->resolveModelPageTarget($layout->conditions ?? []);

        if ($target !== null) {
            [$modelKey, $templateType] = $target;
            $page = \Ccast\Tagixo\Facades\Tagixo::findRoutePagesForModel($modelKey)[$templateType] ?? null;

            return $page !== null && ! empty($page->content['components'] ?? null);
        }

        return (bool) $layout->body_rendered_html;
    }

    /**
     * First layout condition that targets a registered model's pages:
     * model_archive edits the archive page, the other model_* conditions the
     * single page. Returns [modelKey, 'archive'|'single'] or null.
     *
     * @param  array<int, mixed>  $conditions
     * @return array{0: string, 1: string}|null
     */
    protected function resolveModelPageTarget(array $conditions): ?array
    {
        foreach ($conditions as $condition) {
            if (! is_array($condition) || empty($condition['model'])) {
                continue;
            }

            $type = $condition['type'] ?? null;

            if ($type === 'model_archive') {
                return [(string) $condition['model'], 'archive'];
            }

            if (in_array($type, ['model_all', 'model_taxonomy', 'model_record'], true)) {
                return [(string) $condition['model'], 'single'];
            }
        }

        return null;
    }

    public function getConditionTree(): array
    {
        return app(LayoutConditionService::class)->getConditionTree();
    }

    public function searchPages(string $query): array
    {
        return app(LayoutConditionService::class)->searchPages($query);
    }

    public function searchRecords(string $modelKey, string $query): array
    {
        return app(LayoutConditionService::class)->searchRecords($modelKey, $query);
    }

    public function searchTaxonomyTerms(string $taxKey, string $query): array
    {
        return app(LayoutConditionService::class)->searchTaxonomyTerms($taxKey, $query);
    }

    public function getConditionLabel(array $condition): string
    {
        return app(LayoutConditionService::class)->getConditionLabel($condition);
    }
}
