<?php

namespace Ccast\TagixoFilament\Filament\Pages;

use Ccast\Tagixo\Models\SiteScript;
use Ccast\Tagixo\Models\SiteSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ManageSiteSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Visual Builder';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'site-settings';

    protected static ?string $title = 'Site Settings';

    protected string $view = 'tagixo-filament::filament.pages.site-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $state = SiteSettings::settings();

        foreach (SiteScript::LOCATIONS as $location) {
            $value = SiteScript::valueFor($location);
            $state[$location] = $value['content'];
            $state[$location.'_enabled'] = $value['enabled'];
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('settings_tabs')
                    ->tabs([
                        Tab::make('site_info')
                            ->label(__('Site'))
                            ->icon('heroicon-o-globe-alt')
                            ->components([
                                TextInput::make('site_name')->label(__('Site name'))->columnSpanFull(),
                                TextInput::make('default_title')->label(__('Default page title'))->columnSpanFull(),
                                Textarea::make('default_description')->label(__('Default meta description'))->rows(3)->columnSpanFull(),
                                Select::make('favicon_url')
                                    ->label(__('Favicon'))
                                    ->options(fn () => \Ccast\Tagixo\MediaGallery\Models\Media::query()->originals()->where('type', 'image')->get()->pluck('filename', 'path')->toArray())
                                    ->searchable()
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('scripts')
                            ->label(__('Scripts'))
                            ->icon('heroicon-o-code-bracket')
                            ->components([
                                ...$this->locationFields('head'),
                                ...$this->locationFields('body_open'),
                                ...$this->locationFields('body_close'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Build the enable toggle + textarea pair for one injection location.
     *
     * @return array<int, \Filament\Forms\Components\Field>
     */
    protected function locationFields(string $location): array
    {
        $labels = [
            'head' => __('Head — before </head> (analytics, tag manager, verification meta)'),
            'body_open' => __('Body start — right after <body> (e.g. GTM noscript)'),
            'body_close' => __('Body end — before </body> (deferred widgets, chat)'),
        ];

        return [
            Toggle::make($location.'_enabled')
                ->label($labels[$location] ?? $location)
                ->default(true)
                ->columnSpanFull(),
            Textarea::make($location)
                ->hiddenLabel()
                ->rows(8)
                ->columnSpanFull(),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (SiteSettings::KEYS as $key) {
            SiteSettings::set($key, $state[$key] ?? null);
        }

        foreach (SiteScript::LOCATIONS as $location) {
            SiteScript::setFor(
                $location,
                $state[$location] ?? null,
                (bool) ($state[$location.'_enabled'] ?? true),
            );
        }

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }
}
