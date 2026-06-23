<?php

namespace Ccast\TagixoFilament;

use Ccast\TagixoFilament\Filament\Pages\ManageSiteScripts;
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
        $panel->resources([
            PageResource::class,
            LayoutResource::class,
            FormResource::class,
            SliderResource::class,
            MailResource::class,
            MenuResource::class,
        ]);

        if ($this->mediaGallery) {
            $panel->resources([
                MediaResource::class,
            ]);
        }

        $panel->pages([
            ManageSiteScripts::class,
        ]);
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
