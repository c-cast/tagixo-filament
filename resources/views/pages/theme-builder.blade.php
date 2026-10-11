{{--
    Templates of the site, zone by zone. A header and a footer belong to the
    template, so they open it; a body belongs to a page, and for a template
    scoped to a model that page is created on the way to the editor.
--}}
<x-filament-panels::page>
    <div class="space-y-6">
        @forelse ($this->templates() as $template)
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <header class="flex items-start justify-between gap-4 border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-semibold text-gray-950 dark:text-white">
                            {{ $template->name }}
                            @if ($template->is_global)
                                <x-filament::badge color="primary">{{ __('Global') }}</x-filament::badge>
                            @endif
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $this->conditionsSummary($template) }}
                        </p>
                    </div>

                    <x-filament::link :href="$this->settingsUrl($template)" size="sm">
                        {{ __('Settings') }}
                    </x-filament::link>
                </header>

                <div class="grid gap-3 p-4 sm:grid-cols-3">
                    @foreach ($this->zones($template) as $zone)
                        <article @class([
                            'flex flex-col gap-1 rounded-lg border p-3',
                            'border-primary-200 bg-primary-50/50 dark:border-primary-500/30 dark:bg-primary-500/5' => $zone['configured'],
                            'border-dashed border-gray-300 dark:border-white/10' => ! $zone['configured'],
                        ])>
                            <h3 class="text-sm font-medium text-gray-950 dark:text-white">{{ $zone['label'] }}</h3>

                            <p class="text-xs font-medium {{ $zone['configured'] ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}">
                                {{ $zone['configured'] ? __('Built') : __('Empty') }}
                            </p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $zone['description'] }}</p>

                            <div class="mt-2">
                                @if ($zone['url'])
                                    <x-filament::button
                                        :href="$zone['url']"
                                        tag="a"
                                        size="xs"
                                        :outlined="! $zone['configured']"
                                    >
                                        {{ $zone['configured'] ? __('Edit') : __('Build') }}
                                    </x-filament::button>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('Not here') }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <x-filament-tables::empty-state
                icon="heroicon-o-squares-2x2"
                :heading="__('No template yet')"
                :description="__('A page without one wears nothing.')"
            />
        @endforelse
    </div>
</x-filament-panels::page>
