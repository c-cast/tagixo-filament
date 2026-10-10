<?php

namespace Tagixo\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Tagixo\Core\BuilderTypeRegistry;
use Tagixo\Core\Contracts\HasPlugin;
use Tagixo\Core\Tagixo;
use Tagixo\Filament\Resources;

/**
 * Brings Tagixo into a Filament panel: one admin resource per record type the
 * installed builders register, and nothing for the ones they don't.
 *
 * Which packages are installed is asked of the BuilderTypeRegistry, not of the
 * autoloader: an application can drop a type from `tagixo.builder_types` or
 * replace its handler, and the panel has to follow that, not the file system.
 */
class TagixoFilamentPlugin implements Plugin
{
    /**
     * Resource per type. A type whose package is absent is skipped.
     *
     * @var array<string, class-string>
     */
    protected array $resources = [
        'pages' => Resources\PageResource::class,
        'popups' => Resources\PopupResource::class,
        'global-blocks' => Resources\GlobalBlockResource::class,
        'layouts' => Resources\LayoutResource::class,
        'forms' => Resources\FormResource::class,
        'mails' => Resources\MailResource::class,
        'documents' => Resources\DocumentResource::class,
        'sliders' => Resources\SliderResource::class,
    ];

    /** @var list<string>|null */
    protected ?array $only = null;

    /** @var list<string> */
    protected array $except = [];

    /** @var array<string, string> */
    protected array $icons = [];

    protected ?string $navigationGroup = null;

    public function getId(): string
    {
        return 'tagixo';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        // `resources()` replaces the list, and a panel may already have its own
        // (listed or discovered): keep them.
        $panel->resources([...$panel->getResources(), ...array_values($this->resolveResources())]);
    }

    public function boot(Panel $panel): void
    {
        $this->bootPluginsOfPlugins($panel);
    }

    /**
     * The resources this panel gets: declared, not excluded, and belonging to a
     * type the registry knows.
     *
     * @return array<string, class-string>
     */
    public function resolveResources(): array
    {
        $registry = app(BuilderTypeRegistry::class);
        $configured = (array) config('tagixo-filament.resources', []);
        $resources = [...$this->resources, ...array_filter($configured, 'is_string')];

        $resolved = [];

        foreach ($resources as $type => $resource) {
            if (! $registry->has($type) || ! $this->wanted($type) || ! class_exists($resource)) {
                continue;
            }

            $resolved[$type] = $resource;
        }

        return $resolved;
    }

    /**
     * Only these types, in this order.
     *
     * @param  list<string>  $types
     */
    public function only(array $types): static
    {
        $this->only = array_values($types);

        return $this;
    }

    /**
     * @param  list<string>  $types
     */
    public function except(array $types): static
    {
        $this->except = array_values($types);

        return $this;
    }

    /**
     * Replace the resource of one type with your own — a subclass that adds a
     * column, or something written from scratch.
     */
    public function resource(string $type, string $resource): static
    {
        $this->resources[$type] = $resource;

        return $this;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    /**
     * @param  array<string, string>  $icons
     */
    public function icons(array $icons): static
    {
        $this->icons = [...$this->icons, ...$icons];

        return $this;
    }

    public function iconFor(string $type): ?string
    {
        return $this->icons[$type]
            ?? config("tagixo-filament.icons.{$type}")
            ?? config('tagixo-filament.icons.default');
    }

    /**
     * The group every Tagixo resource sits in, unless the panel says otherwise.
     */
    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup ?? config('tagixo-filament.navigation_group');
    }

    protected function wanted(string $type): bool
    {
        if (in_array($type, $this->except, true)) {
            return false;
        }

        return $this->only === null || in_array($type, $this->only, true);
    }

    /**
     * A Tagixo plugin that brings its own admin screens says so by implementing
     * HasPlugin: whatever `getPlugin()` returns is registered here, so a package
     * that adds a content type needs no panel wiring in the application that
     * installs it.
     */
    protected function bootPluginsOfPlugins(Panel $panel): void
    {
        foreach (app(Tagixo::class)->getPlugins() as $plugin) {
            if (! $plugin instanceof HasPlugin) {
                continue;
            }

            $own = $plugin->getPlugin();

            if ($own instanceof Plugin) {
                $own->register($panel);
                $own->boot($panel);
            }
        }
    }
}
