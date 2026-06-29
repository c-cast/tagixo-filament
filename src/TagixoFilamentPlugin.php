<?php

namespace Ccast\TagixoFilament;

use Ccast\TagixoFilament\Filament\Pages\ManageSiteSettings;
use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Ccast\TagixoFilament\Filament\Resources\MediaResource;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;
use Filament\Contracts\Plugin;
use Filament\FilamentManager;
use Filament\Panel;

class TagixoFilamentPlugin implements Plugin
{
    private bool $mediaGallery = false;

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

        $panel->pages([
            ManageSiteSettings::class,
        ]);
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
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): Plugin | FilamentManager
    {
        return filament(app(static::class)->getId());
    }

    public function withMediaGallery(bool $enabled = true): static
    {
        $this->mediaGallery = $enabled;

        return $this;
    }
}
