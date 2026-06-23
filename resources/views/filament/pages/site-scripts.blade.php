<x-filament-panels::page>
    <div class="fi-section-content-ctn max-w-3xl space-y-2 text-sm text-gray-600 dark:text-gray-400">
        <p>
            {{ __('Paste raw markup to inject site-wide into every public page. Use this for analytics, tag managers, site-verification meta tags, or third-party widgets.') }}
        </p>
        <p class="text-amber-600 dark:text-amber-400">
            {{ __('This markup is printed verbatim and runs on every page. Only paste code from sources you trust.') }}
        </p>
    </div>

    <form wire:submit="save" class="mt-6 space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">
                {{ __('Save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
