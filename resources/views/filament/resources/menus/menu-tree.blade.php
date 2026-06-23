@php
    $statePath = $getStatePath();
    $linkTypeOptions = $getLinkTypeOptions();
    $pageOptions = $getPageOptions();
    $blankItem = $getBlankItem();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: @entangle($statePath).live,
            editIndex: null,
            editDraft: {},
            nextId: 0,
            blankItem: @js($blankItem),
            linkTypeOptions: @js($linkTypeOptions),
            pageOptions: @js($pageOptions),

            init() {
                if (! Array.isArray(this.state)) {
                    this.state = [];
                }
                // Stable per-row key so Alpine's x-for reconciles correctly with
                // the DOM nodes SortableJS moves. `__id` is client-only and inert
                // for persistence (PersistsMenuItems only reads known columns).
                this.ensureIds();
            },

            ensureIds() {
                this.state.forEach((item) => {
                    if (item.__id === undefined || item.__id === null) {
                        item.__id = 'mt' + (this.nextId++);
                    }
                });
            },

            /* ---- depth helpers (mirror MenuTreeStructure::normalizeDepths) ---- */
            normalize() {
                let prev = -1;
                this.state.forEach((item) => {
                    let depth = parseInt(item.depth ?? 0, 10) || 0;
                    if (depth < 0) depth = 0;
                    if (depth > prev + 1) depth = prev + 1;
                    item.depth = depth;
                    prev = depth;
                });
                this.state = [...this.state];
            },

            blockLength(index) {
                const depth = this.state[index]?.depth ?? 0;
                let len = 1;
                for (let j = index + 1; j < this.state.length; j++) {
                    if ((this.state[j].depth ?? 0) > depth) len++;
                    else break;
                }
                return len;
            },

            previousSibling(index) {
                const depth = this.state[index]?.depth ?? 0;
                for (let j = index - 1; j >= 0; j--) {
                    const dj = this.state[j].depth ?? 0;
                    if (dj < depth) return null;
                    if (dj === depth) return j;
                }
                return null;
            },

            /* ---- mutations (all client-side; entangle syncs to Livewire) ---- */
            add() {
                const item = JSON.parse(JSON.stringify(this.blankItem));
                item.__id = 'mt' + (this.nextId++);
                this.state.push(item);
                this.normalize();
                this.openEdit(this.state.length - 1);
            },

            remove(index) {
                const len = this.blockLength(index);
                this.state.splice(index, len);
                this.normalize();
            },

            indent(index) {
                if (this.previousSibling(index) === null) return;
                const len = this.blockLength(index);
                for (let k = index; k < index + len; k++) {
                    this.state[k].depth = (this.state[k].depth ?? 0) + 1;
                }
                this.normalize();
            },

            outdent(index) {
                if ((this.state[index]?.depth ?? 0) <= 0) return;
                const len = this.blockLength(index);
                for (let k = index; k < index + len; k++) {
                    this.state[k].depth = Math.max(0, (this.state[k].depth ?? 0) - 1);
                }
                this.normalize();
            },

            moveUp(index) {
                const prev = this.previousSibling(index);
                if (prev === null) return;
                const len = this.blockLength(index);
                const block = this.state.splice(index, len);
                this.state.splice(prev, 0, ...block);
                this.normalize();
            },

            moveDown(index) {
                const depth = this.state[index]?.depth ?? 0;
                const len = this.blockLength(index);
                const next = index + len;
                if (next >= this.state.length || (this.state[next].depth ?? 0) !== depth) return;
                const nextLen = this.blockLength(next);
                const block = this.state.splice(index, len);
                this.state.splice(index + nextLen, 0, ...block);
                this.normalize();
            },

            /* ---- drag reorder via Filament's native x-sortable ---- */
            reorder(order) {
                const current = this.state;
                const next = order
                    .map((key) => current[parseInt(key, 10)])
                    .filter((item) => item !== undefined);
                if (next.length !== current.length) return; // guard malformed payload
                this.state = next;
                this.normalize();
            },

            /* ---- modal editing (working copy committed on save) ---- */
            openEdit(index) {
                if (! this.state[index]) return;
                this.editIndex = index;
                this.editDraft = JSON.parse(JSON.stringify({ ...this.blankItem, ...this.state[index] }));
            },

            saveEdit() {
                if (this.editIndex === null) { this.closeEdit(); return; }
                const depth = this.state[this.editIndex]?.depth ?? 0;
                this.state[this.editIndex] = { ...this.editDraft, depth };
                this.state = [...this.state];
                this.closeEdit();
            },

            closeEdit() {
                this.editIndex = null;
                this.editDraft = {};
            },

            linkTypeLabel(value) {
                const found = this.linkTypeOptions.find((o) => o.value === value);
                return found ? found.label : value;
            },
        }"
        class="space-y-3"
    >
        {{-- Tree --}}
        <div
            x-sortable
            x-on:end="reorder($event.target.sortable.toArray())"
            class="space-y-1"
        >
            <template x-for="(item, i) in state" :key="item.__id ?? i">
                <div
                    x-sortable-item="i"
                    class="fi-input-wrapper flex items-center gap-2 rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/10"
                    :style="`margin-inline-start: ${(item.depth || 0) * 1.75}rem`"
                >
                    <button type="button" x-sortable-handle class="cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 shrink-0">
                        @svg('heroicon-m-bars-2', 'w-4 h-4')
                    </button>

                    <span class="flex-1 min-w-0 truncate text-sm font-medium text-gray-950 dark:text-white">
                        <span x-show="item.label" x-text="item.label"></span>
                        <span x-show="! item.label" class="italic text-gray-400">{{ __('Untitled item') }}</span>
                    </span>

                    <span
                        class="hidden sm:inline-flex shrink-0 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300"
                        x-text="linkTypeLabel(item.target_type)"
                    ></span>

                    <label class="inline-flex items-center shrink-0" :title="'{{ __('Visible') }}'">
                        <input type="checkbox" x-model="item.visible" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-white/5">
                    </label>

                    <div class="flex items-center gap-0.5 shrink-0 text-gray-500 dark:text-gray-400">
                        <button type="button" @click="outdent(i)" :title="'{{ __('Outdent') }}'" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-white/10">@svg('heroicon-m-chevron-left', 'w-4 h-4')</button>
                        <button type="button" @click="indent(i)" :title="'{{ __('Indent') }}'" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-white/10">@svg('heroicon-m-chevron-right', 'w-4 h-4')</button>
                        <button type="button" @click="moveUp(i)" :title="'{{ __('Move up') }}'" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-white/10">@svg('heroicon-m-arrow-up', 'w-4 h-4')</button>
                        <button type="button" @click="moveDown(i)" :title="'{{ __('Move down') }}'" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-white/10">@svg('heroicon-m-arrow-down', 'w-4 h-4')</button>
                        <button type="button" @click="openEdit(i)" :title="'{{ __('Edit') }}'" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-white/10">@svg('heroicon-m-pencil-square', 'w-4 h-4')</button>
                        <button type="button" @click="remove(i)" :title="'{{ __('Delete') }}'" class="rounded p-1 text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10">@svg('heroicon-m-trash', 'w-4 h-4')</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty state --}}
        <div
            x-show="! state || state.length === 0"
            class="rounded-lg border border-dashed border-gray-300 py-6 text-center text-sm text-gray-400 dark:border-white/10"
        >
            {{ __('No menu items yet. Add the first one to get started.') }}
        </div>

        {{-- Add --}}
        <div>
            <x-filament::button type="button" size="sm" color="gray" icon="heroicon-m-plus" x-on:click="add()">
                {{ __('Add item') }}
            </x-filament::button>
        </div>

        {{-- Edit modal (self-contained Alpine overlay) --}}
        <template x-teleport="body">
            <div
                x-show="editIndex !== null"
                x-cloak
                class="fixed inset-0 z-40 flex items-center justify-center p-4"
                x-on:keydown.escape.window="closeEdit()"
            >
                <div class="absolute inset-0 bg-gray-950/50" x-on:click="closeEdit()"></div>

                <div
                    x-show="editIndex !== null"
                    x-transition
                    class="relative z-10 w-full max-w-lg rounded-xl bg-white p-6 shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10"
                >
                    <h3 class="mb-4 text-base font-semibold text-gray-950 dark:text-white">{{ __('Edit menu item') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Label') }}</label>
                            <input type="text" x-model="editDraft.label" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Link type') }}</label>
                            <select x-model="editDraft.target_type" class="fi-select block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                <template x-for="opt in linkTypeOptions" :key="opt.value">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>

                        <div x-show="editDraft.target_type === 'page'">
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Page') }}</label>
                            <select x-model.number="editDraft.target_page_id" class="fi-select block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                <option :value="null">{{ __('— Select a page —') }}</option>
                                <template x-for="opt in pageOptions" :key="opt.value">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>

                        <div x-show="editDraft.target_type !== 'page'">
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Link target') }}</label>
                            <input type="text" x-model="editDraft.target_value" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <p class="mt-1 text-xs text-gray-400">{{ __('URL, route name, or anchor (#section). Depends on the link type.') }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Icon') }}</label>
                                <input type="text" x-model="editDraft.icon" placeholder="heroicon-o-home" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Item CSS class') }}</label>
                                <input type="text" x-model="editDraft.css_class" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            </div>
                        </div>

                        <div class="flex items-center gap-6 pt-1">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-950 dark:text-white">
                                <input type="checkbox" x-model="editDraft.new_tab" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-white/5">
                                {{ __('Open in new tab') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-950 dark:text-white">
                                <input type="checkbox" x-model="editDraft.visible" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-white/5">
                                {{ __('Visible') }}
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <x-filament::button type="button" color="gray" x-on:click="closeEdit()">
                            {{ __('Cancel') }}
                        </x-filament::button>
                        <x-filament::button type="button" icon="heroicon-m-check" x-on:click="saveEdit()">
                            {{ __('Save') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
