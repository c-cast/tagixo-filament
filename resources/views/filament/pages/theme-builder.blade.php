<x-filament-panels::page>
    <div x-data="themeBuilderData()" class="space-y-6">

        {{-- Header bar --}}
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Manage your site templates and assign them to page types.') }}
            </p>
            <button
                @click="handleOpenCreate()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                {{ __('New Template') }}
            </button>
        </div>

        {{-- Template grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;">

            @foreach ($this->getLayouts() as $layout)
                <div class="flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                    {{-- Card header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <span class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $layout->name }}</span>
                        @if ($layout->is_global)
                            <span class="ml-2 shrink-0 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 px-2 py-0.5 rounded-full">
                                {{ __('Global') }}
                            </span>
                        @endif
                    </div>

                    {{-- Sections --}}
                    <div class="flex flex-col gap-2 px-4 py-3">

                        {{-- Header --}}
                        @if ($layout->header_rendered_html)
                            <a href="{{ $this->getBuildUrl($layout->id, 'header') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                style="background:#059669;color:#fff;">
                                <x-heroicon-o-paint-brush class="w-4 h-4 shrink-0"/>{{ __('Header') }}
                            </a>
                        @else
                            <a href="{{ $this->getBuildUrl($layout->id, 'header') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed transition-colors"
                                style="border-color:#d1d5db;color:#6b7280;">
                                <x-heroicon-o-plus class="w-4 h-4 shrink-0"/>{{ __('Header') }}
                            </a>
                        @endif

                        {{-- Body --}}
                        @if ($layout->is_global)
                            <span class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed cursor-not-allowed"
                                style="border-color:#e5e7eb;color:#d1d5db;">
                                <x-heroicon-o-minus class="w-4 h-4 shrink-0"/>{{ __('Body') }}
                            </span>
                        @elseif ($layout->body_rendered_html)
                            <a href="{{ $this->getBuildUrl($layout->id, 'body') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                style="background:#059669;color:#fff;">
                                <x-heroicon-o-paint-brush class="w-4 h-4 shrink-0"/>{{ __('Body') }}
                            </a>
                        @else
                            <a href="{{ $this->getBuildUrl($layout->id, 'body') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed transition-colors"
                                style="border-color:#d1d5db;color:#6b7280;">
                                <x-heroicon-o-plus class="w-4 h-4 shrink-0"/>{{ __('Body') }}
                            </a>
                        @endif

                        {{-- Footer --}}
                        @if ($layout->footer_rendered_html)
                            <a href="{{ $this->getBuildUrl($layout->id, 'footer') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                style="background:#059669;color:#fff;">
                                <x-heroicon-o-paint-brush class="w-4 h-4 shrink-0"/>{{ __('Footer') }}
                            </a>
                        @else
                            <a href="{{ $this->getBuildUrl($layout->id, 'footer') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed transition-colors"
                                style="border-color:#d1d5db;color:#6b7280;">
                                <x-heroicon-o-plus class="w-4 h-4 shrink-0"/>{{ __('Footer') }}
                            </a>
                        @endif

                    </div>

                    {{-- Conditions badges --}}
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex-1">
                        @if ($layout->is_global)
                            <span class="inline-flex text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                {{ __('Default fallback') }}
                            </span>
                        @elseif ($layout->conditions && count($layout->conditions) > 0)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($layout->conditions as $condition)
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">
                                        {{ $this->getConditionLabel($condition) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">{{ __('No conditions set') }}</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    @if (! $layout->is_global)
                        <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                            <button @click="handleOpenEdit({{ $layout->id }})"
                                class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <x-heroicon-o-cog-6-tooth class="w-4 h-4"/>
                                {{ __('Settings') }}
                            </button>
                            <button
                                @click="if (confirm('{{ __('Are you sure you want to delete this layout?') }}')) $wire.deleteLayout({{ $layout->id }})"
                                class="inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 px-2 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors ml-auto">
                                <x-heroicon-o-trash class="w-4 h-4"/>
                                {{ __('Delete') }}
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Add new template card --}}
            <button @click="handleOpenCreate()"
                class="flex flex-col items-center justify-center gap-3 p-8 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 text-gray-400 dark:text-gray-500 hover:border-blue-400 hover:text-blue-500 dark:hover:border-blue-500 dark:hover:text-blue-400 transition-colors cursor-pointer"
                style="min-height:200px;">
                <x-heroicon-o-plus-circle class="w-10 h-10"/>
                <span class="text-sm font-medium">{{ __('New Template') }}</span>
            </button>
        </div>

        {{-- Filament modal — handles z-index, backdrop, and focus-trap natively --}}
        <x-filament::modal id="layout-modal" width="2xl">
            <x-slot name="heading">
                <span x-show="isEditing">{{ __('Template Settings') }}</span>
                <span x-show="! isEditing">{{ __('New Template') }}</span>
            </x-slot>

            <div class="overflow-y-auto" style="max-height:60vh;padding:0 2px;margin:0 -2px;">

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Template Name') }}</label>
                <input type="text" x-model="localName"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    placeholder="{{ __('e.g. Blog Template') }}">
            </div>

            <hr class="border-gray-100 dark:border-gray-700 my-5">

            {{-- Conditions --}}
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Use On') }}</h4>
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">{{ __('This template applies when any of the following conditions match.') }}</p>

                <div class="space-y-3">

                    {{-- ── PAGES section ── --}}
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl">
                        <button type="button" @click="toggleGroup('pages')"
                            class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-700/60 rounded-t-xl text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/80 transition-colors">
                            <span>{{ __('Pages') }}</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="isGroupOpen('pages') ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>

                        <div x-show="isGroupOpen('pages')">
                        {{-- Homepage --}}
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                            <input type="checkbox" :checked="hasHomepage()" @change="toggleHomepage()"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Homepage') }}</span>
                        </label>

                        {{-- Specific Pages checkbox --}}
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                            <input type="checkbox" :checked="specificPagesOpen()" @change="toggleSpecificPages()"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Specific Pages') }}</span>
                        </label>

                        {{-- Specific Pages search (shown when checkbox is on) --}}
                        <div x-show="specificPagesOpen()" class="px-4 pb-3 pl-11 space-y-2 border-t border-gray-100 dark:border-gray-700/50">
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <template x-for="c in pageConditions()" :key="c.value">
                                    <span class="inline-flex items-center gap-1 text-xs bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 pl-2 pr-1 py-0.5 rounded-full">
                                        <span x-text="c.label || ('#' + c.value)"></span>
                                        <button type="button" @click="removePageCondition(c.value)" class="ml-0.5 hover:text-blue-900 dark:hover:text-blue-100 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="relative">
                                <input type="text" x-model="pageSearch" @input="doSearchPages()"
                                    placeholder="{{ __('Search pages…') }}"
                                    class="w-full text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div x-show="pageResults.length > 0"
                                    class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
                                    <template x-for="p in pageResults" :key="p.id">
                                        <button type="button" @click="addPageCondition(p)"
                                            class="w-full text-left text-sm px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition-colors"
                                            x-text="p.title"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    {{-- ── MODEL / TAXONOMY sections ── --}}
                    @foreach ($this->getConditionTree() as $modelKey => $modelDef)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl">
                        <button type="button" @click="toggleGroup('{{ $modelKey }}')"
                            class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-700/60 rounded-t-xl text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/80 transition-colors">
                            <span>{{ $modelDef['label'] }}</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="isGroupOpen('{{ $modelKey }}') ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>

                        <div x-show="isGroupOpen('{{ $modelKey }}')">
                        {{-- All [model] --}}
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                            <input type="checkbox"
                                :checked="hasModelAll('{{ $modelKey }}')"
                                @change="toggleModelAll('{{ $modelKey }}')"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('All') }} {{ $modelDef['label'] }}</span>
                        </label>

                        {{-- Archive [model] --}}
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                            <input type="checkbox"
                                :checked="hasModelArchive('{{ $modelKey }}')"
                                @change="toggleModelArchive('{{ $modelKey }}')"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Archive') }} {{ $modelDef['label'] }}</span>
                        </label>

                        {{-- Taxonomy sub-rows (only for non-taxonomy models) --}}
                        @if (! $modelDef['is_taxonomy'])
                            @foreach ($modelDef['taxonomies'] as $taxKey => $tax)
                            {{-- "By [Taxonomy]" checkbox --}}
                            <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                                <input type="checkbox"
                                    :checked="isTaxOpen('{{ $modelKey }}', '{{ $taxKey }}')"
                                    @change="toggleTaxSection('{{ $modelKey }}', '{{ $taxKey }}')"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('By') }} {{ $tax['label'] }}</span>
                            </label>

                            {{-- Taxonomy search --}}
                            <div x-show="isTaxOpen('{{ $modelKey }}', '{{ $taxKey }}')"
                                class="px-4 pb-3 pl-11 space-y-2 border-t border-gray-100 dark:border-gray-700/50">
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <template x-for="c in getTaxTerms('{{ $modelKey }}', '{{ $taxKey }}')" :key="c.term_id">
                                        <span class="inline-flex items-center gap-1 text-xs bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 pl-2 pr-1 py-0.5 rounded-full">
                                            <span x-text="c.term_label || ('#' + c.term_id)"></span>
                                            <button type="button" @click="removeTaxTerm('{{ $modelKey }}', '{{ $taxKey }}', c.term_id)" class="ml-0.5 hover:text-purple-900 dark:hover:text-purple-100 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                                <div class="relative">
                                    <input type="text" @input="doSearchTax('{{ $taxKey }}', $event.target.value)"
                                        placeholder="{{ __('Search') }} {{ $tax['label'] }}…"
                                        class="w-full text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div x-show="taxResults['{{ $taxKey }}'] && taxResults['{{ $taxKey }}'].length > 0"
                                        class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
                                        <template x-for="t in (taxResults['{{ $taxKey }}'] || [])" :key="t.id">
                                            <button type="button" @click="addTaxTerm('{{ $modelKey }}', '{{ $taxKey }}', t)"
                                                class="w-full text-left text-sm px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition-colors"
                                                x-text="t.label"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif

                        {{-- Specific [model] checkbox --}}
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                            <input type="checkbox"
                                :checked="isSpecificModelOpen('{{ $modelKey }}')"
                                @change="toggleSpecificModel('{{ $modelKey }}')"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Specific') }} {{ $modelDef['label'] }}</span>
                        </label>

                        {{-- Specific record search --}}
                        <div x-show="isSpecificModelOpen('{{ $modelKey }}')"
                            class="px-4 pb-3 pl-11 space-y-2 border-t border-gray-100 dark:border-gray-700/50">
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <template x-for="c in getModelRecords('{{ $modelKey }}')" :key="c.model_id">
                                    <span class="inline-flex items-center gap-1 text-xs bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 pl-2 pr-1 py-0.5 rounded-full">
                                        <span x-text="c.record_label || ('#' + c.model_id)"></span>
                                        <button type="button" @click="removeModelRecord('{{ $modelKey }}', c.model_id)" class="ml-0.5 hover:text-emerald-900 dark:hover:text-emerald-100 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="relative">
                                <input type="text" @input="doSearchRecords('{{ $modelKey }}', $event.target.value)"
                                    placeholder="{{ __('Search') }} {{ $modelDef['label'] }}…"
                                    class="w-full text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div x-show="recordResults['{{ $modelKey }}'] && recordResults['{{ $modelKey }}'].length > 0"
                                    class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
                                    <template x-for="r in (recordResults['{{ $modelKey }}'] || [])" :key="r.id">
                                        <button type="button" @click="addModelRecord('{{ $modelKey }}', r)"
                                            class="w-full text-left text-sm px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition-colors"
                                            x-text="r.label"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>

            </div>{{-- end overflow-y-auto --}}

            <x-slot name="footer">
                <x-filament::button color="gray" @click="$dispatch('close-modal', {id: 'layout-modal'}); resetModalState()">
                    {{ __('Cancel') }}
                </x-filament::button>
                <x-filament::button @click="handleSave()">
                    {{ __('Save') }}
                </x-filament::button>
            </x-slot>
        </x-filament::modal>

    </div>

    <script>
    function themeBuilderData() {
        return {
            isEditing: false,
            groupOpen: {},
            localName: '',
            localConditions: [],
            sectionOpen: {},
            pageSearch: '',
            pageResults: [],
            taxResults: {},
            recordResults: {},

            hasHomepage() {
                return this.localConditions.some(c => c.type === 'homepage');
            },
            pageConditions() {
                return this.localConditions.filter(c => c.type === 'page_id');
            },
            specificPagesOpen() {
                const key = 'pages__specific';
                return this.sectionOpen[key] !== undefined
                    ? this.sectionOpen[key]
                    : this.pageConditions().length > 0;
            },
            isTaxOpen(modelKey, taxKey) {
                const key = modelKey + '__tax__' + taxKey;
                return this.sectionOpen[key] !== undefined
                    ? this.sectionOpen[key]
                    : this.localConditions.some(c => c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey);
            },
            isSpecificModelOpen(modelKey) {
                const key = modelKey + '__specific';
                return this.sectionOpen[key] !== undefined
                    ? this.sectionOpen[key]
                    : this.localConditions.some(c => c.type === 'model_record' && c.model === modelKey);
            },
            hasModelAll(modelKey) {
                return this.localConditions.some(c => c.type === 'model_all' && c.model === modelKey);
            },
            hasModelArchive(modelKey) {
                return this.localConditions.some(c => c.type === 'model_archive' && c.model === modelKey);
            },
            getTaxTerms(modelKey, taxKey) {
                return this.localConditions.filter(c => c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey);
            },
            getModelRecords(modelKey) {
                return this.localConditions.filter(c => c.type === 'model_record' && c.model === modelKey);
            },

            resetModalState() {
                this.localName = '';
                this.localConditions = [];
                this.sectionOpen = {};
                this.groupOpen = {};
                this.pageSearch = '';
                this.pageResults = [];
                this.taxResults = {};
                this.recordResults = {};
            },

            isGroupOpen(key) {
                if (this.groupOpen[key] !== undefined) return this.groupOpen[key];
                if (key === 'pages') return this.hasHomepage() || this.pageConditions().length > 0;
                return this.localConditions.some(c => c.model === key);
            },
            toggleGroup(key) {
                this.groupOpen = { ...this.groupOpen, [key]: !this.isGroupOpen(key) };
            },

            handleOpenCreate() {
                this.resetModalState();
                this.isEditing = false;
                this.$dispatch('open-modal', { id: 'layout-modal' });
            },
            async handleOpenEdit(id) {
                this.resetModalState();
                const data = await this.$wire.openEditModal(id);
                this.localName = data.name;
                this.localConditions = JSON.parse(JSON.stringify(data.conditions || []));
                this.isEditing = true;
                this.$dispatch('open-modal', { id: 'layout-modal' });
            },
            async handleSave() {
                await this.$wire.saveModal(this.localName, this.localConditions);
                this.resetModalState();
            },

            toggleHomepage() {
                if (this.hasHomepage()) {
                    this.localConditions = this.localConditions.filter(c => c.type !== 'homepage');
                } else {
                    this.localConditions.push({ type: 'homepage' });
                }
            },
            toggleSpecificPages() {
                if (this.specificPagesOpen()) {
                    this.localConditions = this.localConditions.filter(c => c.type !== 'page_id');
                    this.sectionOpen = { ...this.sectionOpen, 'pages__specific': false };
                } else {
                    this.sectionOpen = { ...this.sectionOpen, 'pages__specific': true };
                }
            },
            async doSearchPages() {
                if (this.pageSearch.length < 2) { this.pageResults = []; return; }
                this.pageResults = await this.$wire.searchPages(this.pageSearch);
            },
            addPageCondition(page) {
                if (!this.pageConditions().some(c => String(c.value) === String(page.id))) {
                    this.localConditions.push({ type: 'page_id', value: page.id, label: page.title });
                }
                this.pageSearch = '';
                this.pageResults = [];
            },
            removePageCondition(pageId) {
                this.localConditions = this.localConditions.filter(c =>
                    !(c.type === 'page_id' && String(c.value) === String(pageId))
                );
            },

            toggleModelAll(modelKey) {
                if (this.hasModelAll(modelKey)) {
                    this.localConditions = this.localConditions.filter(c =>
                        !(c.type === 'model_all' && c.model === modelKey)
                    );
                } else {
                    this.localConditions = this.localConditions.filter(c =>
                        !((c.type === 'model_taxonomy' || c.type === 'model_record' || c.type === 'model_archive') && c.model === modelKey)
                    );
                    const updated = { ...this.sectionOpen };
                    Object.keys(updated).forEach(k => { if (k.startsWith(modelKey + '__')) delete updated[k]; });
                    this.sectionOpen = updated;
                    this.localConditions.push({ type: 'model_all', model: modelKey });
                }
            },
            toggleModelArchive(modelKey) {
                if (this.hasModelArchive(modelKey)) {
                    this.localConditions = this.localConditions.filter(c =>
                        !(c.type === 'model_archive' && c.model === modelKey)
                    );
                } else {
                    this.localConditions = this.localConditions.filter(c =>
                        !((c.type === 'model_all' || c.type === 'model_taxonomy' || c.type === 'model_record') && c.model === modelKey)
                    );
                    const updated = { ...this.sectionOpen };
                    Object.keys(updated).forEach(k => { if (k.startsWith(modelKey + '__')) delete updated[k]; });
                    this.sectionOpen = updated;
                    this.localConditions.push({ type: 'model_archive', model: modelKey });
                }
            },
            toggleTaxSection(modelKey, taxKey) {
                const key = modelKey + '__tax__' + taxKey;
                if (this.isTaxOpen(modelKey, taxKey)) {
                    this.localConditions = this.localConditions.filter(c =>
                        !(c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey)
                    );
                    this.sectionOpen = { ...this.sectionOpen, [key]: false };
                } else {
                    this.localConditions = this.localConditions.filter(c =>
                        !((c.type === 'model_all' || c.type === 'model_archive') && c.model === modelKey)
                    );
                    this.sectionOpen = { ...this.sectionOpen, [key]: true };
                }
            },
            async doSearchTax(taxKey, query) {
                if (query.length < 2) { this.taxResults = { ...this.taxResults, [taxKey]: [] }; return; }
                const res = await this.$wire.searchTaxonomyTerms(taxKey, query);
                this.taxResults = { ...this.taxResults, [taxKey]: res };
            },
            addTaxTerm(modelKey, taxKey, term) {
                if (!this.getTaxTerms(modelKey, taxKey).some(c => String(c.term_id) === String(term.id))) {
                    this.localConditions.push({ type: 'model_taxonomy', model: modelKey, taxonomy: taxKey, term_id: term.id, term_label: term.label });
                }
                this.taxResults = { ...this.taxResults, [taxKey]: [] };
            },
            removeTaxTerm(modelKey, taxKey, termId) {
                this.localConditions = this.localConditions.filter(c =>
                    !(c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey && String(c.term_id) === String(termId))
                );
            },
            toggleSpecificModel(modelKey) {
                const key = modelKey + '__specific';
                if (this.isSpecificModelOpen(modelKey)) {
                    this.localConditions = this.localConditions.filter(c =>
                        !(c.type === 'model_record' && c.model === modelKey)
                    );
                    this.sectionOpen = { ...this.sectionOpen, [key]: false };
                } else {
                    this.localConditions = this.localConditions.filter(c =>
                        !((c.type === 'model_all' || c.type === 'model_archive') && c.model === modelKey)
                    );
                    this.sectionOpen = { ...this.sectionOpen, [key]: true };
                }
            },
            async doSearchRecords(modelKey, query) {
                if (query.length < 2) { this.recordResults = { ...this.recordResults, [modelKey]: [] }; return; }
                const res = await this.$wire.searchRecords(modelKey, query);
                this.recordResults = { ...this.recordResults, [modelKey]: res };
            },
            addModelRecord(modelKey, record) {
                if (!this.getModelRecords(modelKey).some(c => String(c.model_id) === String(record.id))) {
                    this.localConditions.push({ type: 'model_record', model: modelKey, model_id: record.id, record_label: record.label });
                }
                this.recordResults = { ...this.recordResults, [modelKey]: [] };
            },
            removeModelRecord(modelKey, recordId) {
                this.localConditions = this.localConditions.filter(c =>
                    !(c.type === 'model_record' && c.model === modelKey && String(c.model_id) === String(recordId))
                );
            },
        };
    }
    </script>
</x-filament-panels::page>
