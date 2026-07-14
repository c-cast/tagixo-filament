@php
    $statePath = $getStatePath();
    $linkTypeOptions = $getLinkTypeOptions();
    $pageOptions = $getPageOptions();
    $blankItem = $getBlankItem();
    $dropdownTypeOptions = $getDropdownTypeOptions();
    $iconSets = $getIconSets();
@endphp

{{-- The tree's Alpine logic lives in a factory so transient drag state can be
     kept in CLOSURE variables (non-reactive) — that avoids the orphaned-effect
     errors you get when SortableJS moves x-for nodes. Reorder uses Alpine's
     own x-sort plugin (x-for-aware); horizontal drag controls the depth. --}}
<script>
    if (! window.tagixoMenuTree) {
        window.tagixoMenuTree = (config) => {
            const STEP = 28;   // px of horizontal drag per nesting level
            const REM = 1.75;  // rem of indent per level (matches the row style)
            let drag = null;   // private, non-reactive transient drag state

            return {
                editIndex: null,
                editDraft: {},
                nextId: 0,
                blankItem: config.blankItem,
                linkTypeOptions: config.linkTypeOptions,
                pageOptions: config.pageOptions,
                dropdownTypeOptions: config.dropdownTypeOptions,
                iconSets: config.iconSets || [],

                // icon picker state
                iconPickerOpen: false,
                iconSearch: '',
                iconActiveSet: (config.iconSets && config.iconSets[0]) ? config.iconSets[0].name : null,
                iconSvgCache: {},
                iconVisibleCount: 60,
                iconObs: null,

                // collapse/expand state
                collapsedKeys: new Set(),

                get iconFilteredIcons() {
                    const set = this.iconSets.find(s => s.name === this.iconActiveSet);
                    if (!set) return [];
                    const q = this.iconSearch.trim().toLowerCase();
                    const ids = [];
                    const variants = set.variants || [];
                    if (variants.length > 0) {
                        const variant = variants[0];
                        for (const name of (set.icons || [])) {
                            if (!q || name.includes(q)) ids.push(set.name + '/' + variant.prefix + name);
                        }
                    } else {
                        for (const name of (set.icons || [])) {
                            if (!q || name.includes(q)) ids.push(set.name + '/' + name);
                        }
                    }
                    return ids;
                },

                get iconVisibleIcons() {
                    return this.iconFilteredIcons.slice(0, this.iconVisibleCount);
                },

                init() {
                    if (! Array.isArray(this.state)) {
                        this.state = [];
                    }
                    this.ensureIds();
                    this.bindDrag();
                    this.$watch('editDraft.icon', (id) => {
                        if (id && !this.iconSvgCache[id]) {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                            fetch('/tagixo/icons/batch', {
                                method: 'POST', credentials: 'same-origin',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ ids: [id] }),
                            }).then(r => r.ok ? r.json() : null).then(d => { if (d) this.iconSvgCache = { ...this.iconSvgCache, ...d }; }).catch(() => {});
                        }
                    });
                },

                ensureIds() {
                    this.state.forEach((it) => {
                        if (it.__id === undefined || it.__id === null) {
                            it.__id = 'mt' + (this.nextId++);
                        }
                    });
                },

                /* ---- depth helpers ---- */
                normalize() {
                    let prev = -1;
                    this.state.forEach((it) => {
                        let d = parseInt(it.depth ?? 0, 10) || 0;
                        if (d < 0) d = 0;
                        if (d > prev + 1) d = prev + 1;
                        it.depth = d;
                        prev = d;
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

                indexOfId(id) {
                    return this.state.findIndex((s) => s.__id === id);
                },

                /* ---- mutations ---- */
                add() {
                    const it = JSON.parse(JSON.stringify(this.blankItem));
                    it.__id = 'mt' + (this.nextId++);
                    this.state.push(it);
                    this.normalize();
                    this.openEdit(this.state.length - 1);
                },

                remove(index) {
                    const len = this.blockLength(index);
                    this.state.splice(index, len);
                    this.normalize();
                },

                /* ---- drag ---- */
                bindDrag() {
                    const root = this.$root;

                    root.addEventListener('pointerdown', (e) => {
                        const handle = e.target.closest('.menu-tree-handle');
                        if (! handle || ! root.contains(handle)) return;
                        const row = handle.closest('[data-mt-id]');
                        if (! row) return;
                        const id = row.getAttribute('data-mt-id');
                        const idx = this.indexOfId(id);
                        const ul = root.querySelector('[x-sortable]');
                        const sortable = ul ? ul.sortable : null;
                        drag = {
                            startX: e.clientX,
                            lastX: e.clientX,
                            startDepth: this.state[idx]?.depth ?? 0,
                            depth: this.state[idx]?.depth ?? 0,
                            el: row,
                            id,
                            sortable,
                            originalOrder: sortable ? sortable.toArray() : [],
                        };
                    }, true);

                    const move = (e) => {
                        if (! drag) return;
                        const x = e.clientX != null ? e.clientX
                            : (e.touches && e.touches[0] ? e.touches[0].clientX : null);
                        if (x == null) return;
                        drag.lastX = x;
                        this.renderGhost();
                    };
                    document.addEventListener('pointermove', move, true);
                    document.addEventListener('dragover', move, true);

                    document.addEventListener('pointerup', () => {
                        if (drag && drag.el) drag.el.style.marginInlineStart = '';
                    }, true);
                },

                renderGhost() {
                    if (! drag || ! drag.el) return;
                    const rows = Array.from(drag.el.parentElement.querySelectorAll('[data-mt-id]'));
                    const i = rows.indexOf(drag.el);
                    const prevEl = i > 0 ? rows[i - 1] : null;
                    const prevId = prevEl ? prevEl.getAttribute('data-mt-id') : null;
                    const prevDepth = prevId !== null ? (this.state[this.indexOfId(prevId)]?.depth ?? 0) : -1;
                    const maxDepth = prevEl ? prevDepth + 1 : 0;

                    let d = drag.startDepth + Math.round((drag.lastX - drag.startX) / STEP);
                    d = Math.max(0, Math.min(d, maxDepth));
                    drag.depth = d;
                    drag.el.style.marginInlineStart = (d * REM) + 'rem';
                },

                onDragEnd(e) {
                    const sortable = (drag && drag.sortable) || (e && e.target && e.target.sortable);
                    const newOrder = sortable ? sortable.toArray().map(Number) : null;

                    if (drag && drag.el) drag.el.style.marginInlineStart = '';

                    if (! newOrder || ! drag) { drag = null; return; }

                    if (drag.originalOrder && drag.originalOrder.length) {
                        sortable.sort(drag.originalOrder, false);
                    }

                    const dragPos = this.indexOfId(drag.id);
                    const candidate = drag.depth;
                    drag = null;
                    if (dragPos < 0) return;

                    const len = this.blockLength(dragPos);
                    const block = this.state.slice(dragPos, dragPos + len);
                    const inBlock = new Set();
                    for (let k = 0; k < len; k++) inBlock.add(dragPos + k);

                    const result = [];
                    for (const pos of newOrder) {
                        if (pos === dragPos) {
                            for (const b of block) result.push(b);
                        } else if (! inBlock.has(pos)) {
                            result.push(this.state[pos]);
                        }
                    }
                    if (result.length !== this.state.length) return;

                    const at = result.indexOf(block[0]);
                    const above = at > 0 ? result[at - 1] : null;
                    const maxDepth = above ? (above.depth ?? 0) + 1 : 0;
                    const finalDepth = Math.max(0, Math.min(candidate ?? (block[0].depth ?? 0), maxDepth));
                    const delta = finalDepth - (block[0].depth ?? 0);
                    block.forEach((b) => { b.depth = Math.max(0, (b.depth ?? 0) + delta); });

                    this.state = result;
                    this.normalize();
                },

                /* ---- modal editing ---- */
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

                /* ---- collapse/expand ---- */
                hasChildren(i) {
                    const depth = this.state[i]?.depth ?? 0;
                    return (this.state[i + 1]?.depth ?? -1) > depth;
                },

                itemKey(item, i) {
                    return String(item.__id || i);
                },

                toggleCollapse(item, i) {
                    const k = this.itemKey(item, i);
                    const s = new Set(this.collapsedKeys);
                    if (s.has(k)) s.delete(k); else s.add(k);
                    this.collapsedKeys = s;
                },

                isHidden(i) {
                    const iDepth = this.state[i]?.depth ?? 0;
                    for (let j = i - 1; j >= 0; j--) {
                        const jDepth = this.state[j]?.depth ?? 0;
                        if (jDepth < iDepth) {
                            if (this.collapsedKeys.has(this.itemKey(this.state[j], j))) return true;
                        } else {
                            break;
                        }
                    }
                    return false;
                },

                /* ---- icon picker ---- */
                openIconPicker() {
                    this.iconPickerOpen = true;
                    this.iconVisibleCount = 60;
                    this.$nextTick(() => {
                        this.iconFetchVisible();
                        this.iconSetupObserver();
                    });
                },
                closeIconPicker() {
                    this.iconPickerOpen = false;
                    if (this.iconObs) { this.iconObs.disconnect(); this.iconObs = null; }
                },
                iconSelect(id) {
                    this.editDraft.icon = id;
                    this.closeIconPicker();
                },
                iconClear() {
                    this.editDraft.icon = null;
                },
                async iconFetchVisible() {
                    const missing = this.iconVisibleIcons.filter(id => !this.iconSvgCache[id]);
                    if (!missing.length) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    try {
                        const res = await fetch('/tagixo/icons/batch', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ ids: missing }),
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        this.iconSvgCache = { ...this.iconSvgCache, ...data };
                    } catch {}
                },
                iconSetupObserver() {
                    if (this.iconObs) this.iconObs.disconnect();
                    const grid = this.$refs.iconGrid;
                    const sentinel = this.$refs.iconSentinel;
                    if (!grid || !sentinel) return;
                    this.iconObs = new IntersectionObserver((entries) => {
                        if (entries[0]?.isIntersecting && this.iconVisibleCount < this.iconFilteredIcons.length) {
                            this.iconVisibleCount += 60;
                            this.$nextTick(() => this.iconFetchVisible());
                        }
                    }, { root: grid, threshold: 0.1 });
                    this.iconObs.observe(sentinel);
                },
                iconSetChanged() {
                    this.iconVisibleCount = 60;
                    this.$nextTick(() => { this.iconFetchVisible(); this.iconSetupObserver(); });
                },
                iconSearchChanged() {
                    this.iconVisibleCount = 60;
                    this.$nextTick(() => { this.iconFetchVisible(); this.iconSetupObserver(); });
                },
            };
        };
    }
</script>

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: @entangle($statePath),
            ...window.tagixoMenuTree({
                blankItem: @js($blankItem),
                linkTypeOptions: @js($linkTypeOptions),
                pageOptions: @js($pageOptions),
                dropdownTypeOptions: @js($dropdownTypeOptions),
                iconSets: @js($iconSets),
            }),
        }"
        class="space-y-3"
    >
        {{-- Tree --}}
        <ul
            x-sortable
            x-on:end="onDragEnd($event)"
            class="space-y-1 list-none m-0 p-0"
        >
            <template x-for="(item, i) in state" :key="item.__id">
                <li
                    :x-sortable-item="i"
                    :data-mt-id="item.__id"
                    class="fi-input-wrapper flex items-center gap-2 rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/10"
                    :style="`margin-inline-start: ${(item.depth || 0) * 1.75}rem`"
                    x-show="!isHidden(i)"
                >
                    {{-- Collapse/expand toggle --}}
                    <button
                        x-show="hasChildren(i)"
                        type="button"
                        @click="toggleCollapse(item, i)"
                        :title="collapsedKeys.has(itemKey(item, i)) ? '{{ __('Expand') }}' : '{{ __('Collapse') }}'"
                        class="shrink-0 rounded p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <template x-if="collapsedKeys.has(itemKey(item, i))">
                            @svg('heroicon-m-chevron-right', 'w-3 h-3')
                        </template>
                        <template x-if="!collapsedKeys.has(itemKey(item, i))">
                            @svg('heroicon-m-chevron-down', 'w-3 h-3')
                        </template>
                    </button>
                    <span x-show="!hasChildren(i)" class="w-4 shrink-0"></span>

                    <button type="button" x-sortable-handle class="menu-tree-handle cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 shrink-0" :title="'{{ __('Drag to reorder; drag right/left to change level') }}'">
                        @svg('heroicon-m-bars-2', 'w-4 h-4')
                    </button>

                    <span class="flex-1 min-w-0 truncate text-sm font-medium text-gray-950 dark:text-white">
                        <span x-show="item.label" x-text="item.label"></span>
                        <span x-show="! item.label" class="italic text-gray-400">{{ __('Untitled item') }}</span>
                    </span>

                    <span
                        x-show="item.dropdown_type === 'mega'"
                        class="hidden sm:inline-flex shrink-0 rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"
                    >{{ __('Mega menu') }}</span>

                    <span
                        class="hidden sm:inline-flex shrink-0 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300"
                        x-text="linkTypeLabel(item.target_type)"
                    ></span>

                    <label class="inline-flex items-center shrink-0" :title="'{{ __('Visible') }}'">
                        <x-filament::input.checkbox x-model="item.visible" />
                    </label>

                    <div class="flex items-center gap-0.5 shrink-0 text-gray-500 dark:text-gray-400">
                        <button type="button" @click="openEdit(i)" :title="'{{ __('Edit') }}'" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-white/10">@svg('heroicon-m-pencil-square', 'w-4 h-4')</button>
                        <button type="button" @click="remove(i)" :title="'{{ __('Delete') }}'" class="rounded p-1 text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10">@svg('heroicon-m-trash', 'w-4 h-4')</button>
                    </div>
                </li>
            </template>
        </ul>

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
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" x-model="editDraft.label" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Link type') }}</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select x-model="editDraft.target_type">
                                    <template x-for="opt in linkTypeOptions" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div x-show="editDraft.target_type === 'page'">
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Page') }}</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select x-model.number="editDraft.target_page_id">
                                    <option :value="null">{{ __('— Select a page —') }}</option>
                                    <template x-for="opt in pageOptions" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div x-show="editDraft.target_type !== 'page'">
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Link target') }}</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" x-model="editDraft.target_value" />
                            </x-filament::input.wrapper>
                            <p class="mt-1 text-xs text-gray-400">{{ __('URL, route name, or anchor (#section). Depends on the link type.') }}</p>
                        </div>

                        <div x-show="(state[editIndex]?.depth ?? 0) === 0">
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Dropdown type') }}</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select x-model="editDraft.dropdown_type">
                                    <template x-for="opt in dropdownTypeOptions" :key="String(opt.value)">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Icon') }}</label>
                            <div class="relative">
                                <button
                                    type="button"
                                    @click="iconPickerOpen ? closeIconPicker() : openIconPicker()"
                                    class="fi-input-wrapper flex w-full items-center gap-2 rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/10 text-sm text-gray-950 dark:text-white"
                                >
                                    <span x-show="editDraft.icon && iconSvgCache[editDraft.icon]" class="w-5 h-5 shrink-0 [&>svg]:w-5 [&>svg]:h-5" x-html="iconSvgCache[editDraft.icon] || ''"></span>
                                    <span x-show="!editDraft.icon" class="text-gray-400 flex-1 text-left">{{ __('Pick an icon…') }}</span>
                                    <span x-show="editDraft.icon" x-text="editDraft.icon" class="flex-1 text-left truncate"></span>
                                    <button x-show="editDraft.icon" type="button" @click.stop="iconClear()" class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        @svg('heroicon-m-x-mark', 'w-4 h-4')
                                    </button>
                                </button>

                                <div
                                    x-show="iconPickerOpen"
                                    x-cloak
                                    @click.outside="closeIconPicker()"
                                    class="absolute z-50 mt-1 left-0 right-0 rounded-xl bg-white shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10"
                                    style="min-width: 20rem;"
                                >
                                    <div class="flex gap-1 border-b border-gray-100 dark:border-white/10 p-2 overflow-x-auto">
                                        <template x-for="set in iconSets" :key="set.name">
                                            <button
                                                type="button"
                                                @click="iconActiveSet = set.name; iconSetChanged()"
                                                :class="iconActiveSet === set.name
                                                    ? 'bg-primary-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20'"
                                                class="shrink-0 rounded-md px-2 py-1 text-xs font-medium"
                                                x-text="set.label"
                                            ></button>
                                        </template>
                                    </div>

                                    <div class="p-2 border-b border-gray-100 dark:border-white/10">
                                        <x-filament::input.wrapper>
                                            <x-filament::input
                                                type="text"
                                                x-model="iconSearch"
                                                @input="iconSearchChanged()"
                                                placeholder="{{ __('Search icons…') }}"
                                            />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div x-ref="iconGrid" style="max-height: 16rem; overflow-y: auto;" class="p-2">
                                        <div class="grid gap-1" style="grid-template-columns: repeat(auto-fill, minmax(2rem, 1fr));">
                                            <template x-for="id in iconVisibleIcons" :key="id">
                                                <button
                                                    type="button"
                                                    @click="iconSelect(id)"
                                                    :title="id"
                                                    :class="editDraft.icon === id ? 'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'hover:bg-gray-100 dark:hover:bg-white/10'"
                                                    class="flex items-center justify-center rounded p-1 w-8 h-8 [&>svg]:w-5 [&>svg]:h-5 text-gray-700 dark:text-gray-300"
                                                >
                                                    <span x-show="iconSvgCache[id]" x-html="iconSvgCache[id] || ''"></span>
                                                    <span x-show="!iconSvgCache[id]" class="w-4 h-4 rounded bg-gray-200 dark:bg-white/10 animate-pulse"></span>
                                                </button>
                                            </template>
                                        </div>
                                        <div x-ref="iconSentinel" style="height: 1px;"></div>
                                        <div x-show="iconFilteredIcons.length === 0" class="py-6 text-center text-sm text-gray-400">
                                            {{ __('No icons found.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 pt-1">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-950 dark:text-white">
                                <x-filament::input.checkbox x-model="editDraft.new_tab" />
                                {{ __('Open in new tab') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-950 dark:text-white">
                                <x-filament::input.checkbox x-model="editDraft.visible" />
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
