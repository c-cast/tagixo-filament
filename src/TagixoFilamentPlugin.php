<?php

namespace Ccast\TagixoFilament;

use Ccast\TagixoFilament\Filament\Pages\ThemeBuilderPage;
use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Ccast\TagixoFilament\Filament\Resources\MediaResource;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;
use Ccast\Tagixo\Contracts\HasPlugin;
use Ccast\Tagixo\Tagixo;
use Ccast\TagixoFilament\Forms\PropTypes\FilamentTablePropType;
use Filament\Contracts\Plugin;
use Filament\FilamentManager;
use Filament\Panel;

class TagixoFilamentPlugin implements Plugin
{
    private bool $mediaGallery = false;

    private ?string $formTarget = null;

    public function getId(): string
    {
        return 'tagixo';
    }

    public function register(Panel $panel): void
    {
        // The resource list is config-driven: comment out a line in
        // config/tagixo-filament.php to hide that builder from the panel.
        // Fall back to the package defaults when the config is unavailable.
        $resources = array_values(array_filter(
            (array) config('tagixo-filament.resources', $this->defaultResources()),
            fn ($resource) => is_string($resource) && class_exists($resource),
        ));

        // The fluent flag still adds its resource (deduped), so existing
        // ->withMediaGallery() call sites keep working alongside the config.
        if ($this->mediaGallery && ! in_array(MediaResource::class, $resources, true)) {
            $resources[] = MediaResource::class;
        }

        $panel->resources($resources);
        $panel->pages([ThemeBuilderPage::class]);
    }

    /**
     * Default resources used when config/tagixo-filament.php is not loaded.
     *
     * @return array<int, class-string>
     */
    private function defaultResources(): array
    {
        return [
            PageResource::class,
            LayoutResource::class,
            MenuResource::class,
            FormResource::class,
            SliderResource::class,
            MailResource::class,
        ];
    }

    public function boot(Panel $panel): void
    {
        if ($this->formTarget !== null) {
            app(Tagixo::class)->lockFormTarget($this->formTarget);
        }

        app(Tagixo::class)->extendFormModule('*', ['table' => FilamentTablePropType::class]);

        foreach (app(Tagixo::class)->getPlugins() as $plugin) {
            if (! ($plugin instanceof HasPlugin)) {
                continue;
            }

            $sub = $plugin->getPlugin();

            if ($sub instanceof Plugin) {
                $sub->register($panel);
                $sub->boot($panel);
            }
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): Plugin | FilamentManager
    {
        return filament(app(static::class)->getId());
    }

    /**
     * Lock all forms in this panel to a specific target ('universal' or 'app').
     * Hides the target selector — the user cannot change it.
     */
    public function formTarget(string $target): static
    {
        $this->formTarget = $target;

        return $this;
    }

    public function withMediaGallery(bool $enabled = true): static
    {
        $this->mediaGallery = $enabled;

        return $this;
    }
}
