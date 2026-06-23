<?php

namespace Ccast\TagixoFilament\Filament\Pages;

use Ccast\Tagixo\Models\SiteScript;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

/**
 * Admin settings page to manage site-wide custom scripts.
 *
 * Edits three raw markup blobs — one per injection location (head, body-open,
 * body-close) — persisted to {@see SiteScript} and printed verbatim into every
 * public page by the Tagixo frontend layout.
 */
class ManageSiteScripts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-code-bracket';

    protected static string|\UnitEnum|null $navigationGroup = 'Visual Builder';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'site-scripts';

    protected static ?string $title = 'Site Scripts';

    protected string $view = 'tagixo-filament::filament.pages.site-scripts';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $state = [];

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
                ...$this->locationFields('head'),
                ...$this->locationFields('body_open'),
                ...$this->locationFields('body_close'),
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

        foreach (SiteScript::LOCATIONS as $location) {
            SiteScript::setFor(
                $location,
                $state[$location] ?? null,
                (bool) ($state[$location.'_enabled'] ?? true),
            );
        }

        Notification::make()
            ->title(__('Site scripts saved'))
            ->success()
            ->send();
    }
}
