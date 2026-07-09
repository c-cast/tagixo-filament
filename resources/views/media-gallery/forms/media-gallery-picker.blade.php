@php
    $statePath = $getStatePath();
    $multiple = $isMultiple();
    $maxFiles = $getMaxFiles();
    $acceptedTypes = $getAcceptedTypes();
    $placeholder = $getPlaceholder() ?? 'Click to select a media';
    $disabled = $isDisabled();
    $id = $getId();
@endphp

@once
<style>
    .tgx-fp-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background-color: rgba(0,0,0,.5);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .tgx-fp-dialog {
        position: relative;
        width: min(90vw, 1200px);
        max-height: 80vh;
        background: var(--p-content-background, #fff);
        border-radius: var(--p-border-radius-md, .375rem);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
    }
    .tgx-fp-header {
        padding: .75rem 1rem;
        border-bottom: 1px solid var(--p-content-border-color, #e5e7eb);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .tgx-fp-title { font-size: 1.125rem; font-weight: 600; }
    .tgx-fp-close {
        background: none; border: none; cursor: pointer; padding: .25rem;
        border-radius: var(--p-border-radius-md, .375rem);
        color: var(--p-text-muted-color, #6b7280);
        display: flex; align-items: center;
    }
    .tgx-fp-close:hover { background: var(--p-content-hover-background, #f9fafb); }
    .tgx-fp-tabs {
        display: flex;
        border-bottom: 1px solid var(--p-content-border-color, #e5e7eb);
    }
    .tgx-fp-tab {
        padding: .625rem 1rem; border: none; background: none; cursor: pointer;
        font-weight: 500; color: var(--p-text-muted-color, #6b7280);
        border-bottom: 2px solid transparent; margin-bottom: -1px; font-size: .875rem;
    }
    .tgx-fp-tab.active { color: var(--p-primary-color, #3b82f6); border-bottom-color: var(--p-primary-color, #3b82f6); }
    .tgx-fp-body { flex: 1; overflow: hidden; display: flex; flex-direction: column; }
    .tgx-fp-tab-pane { flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: .75rem 1rem; }
    .tgx-fp-toolbar {
        display: flex; gap: .5rem; margin-bottom: .75rem; flex-wrap: wrap; align-items: center;
    }
    .tgx-fp-search {
        flex: 1; min-width: 12rem; padding: .4rem .75rem;
        border: 1px solid var(--p-content-border-color, #e5e7eb);
        border-radius: var(--p-border-radius-md, .375rem);
        background: var(--p-content-background, #fff);
        font-size: .875rem; outline: none; color: inherit;
    }
    .tgx-fp-search:focus { border-color: var(--p-primary-color, #3b82f6); }
    .tgx-fp-select {
        padding: .4rem .6rem;
        border: 1px solid var(--p-content-border-color, #e5e7eb);
        border-radius: var(--p-border-radius-md, .375rem);
        background: var(--p-content-background, #fff);
        font-size: .875rem; outline: none; color: inherit;
    }
    .tgx-fp-view-toggle { display: flex; border: 1px solid var(--p-content-border-color, #e5e7eb); border-radius: var(--p-border-radius-md, .375rem); overflow: hidden; }
    .tgx-fp-view-btn { padding: .3rem .5rem; border: none; background: none; cursor: pointer; color: var(--p-text-muted-color, #6b7280); }
    .tgx-fp-view-btn.active { background: var(--p-primary-color, #3b82f6); color: #fff; }
    .tgx-fp-items-wrap { flex: 1; overflow-y: auto; }
    .tgx-fp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: .75rem; }
    .tgx-fp-list-view { display: flex; flex-direction: column; gap: .5rem; }
    .tgx-fp-item {
        position: relative; cursor: pointer; border-radius: var(--p-border-radius-md, .375rem);
        overflow: hidden; border: 2px solid transparent; transition: border-color .15s;
    }
    .tgx-fp-item.selected { border-color: var(--p-primary-color, #3b82f6); }
    .tgx-fp-item-grid .tgx-fp-thumb { aspect-ratio: 1; background: var(--p-content-hover-background, #f9fafb); }
    .tgx-fp-item-list { display: flex; align-items: center; gap: .75rem; padding: .5rem; border: 1px solid var(--p-content-border-color, #e5e7eb); border-radius: var(--p-border-radius-md, .375rem); }
    .tgx-fp-item-list.selected { border-color: var(--p-primary-color, #3b82f6); background: color-mix(in sRGB, var(--p-primary-color, #3b82f6) 5%, transparent); }
    .tgx-fp-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .tgx-fp-thumb-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--p-text-muted-color, #6b7280); }
    .tgx-fp-check {
        position: absolute; top: .35rem; left: .35rem; width: 1rem; height: 1rem;
        border-radius: .25rem; border: 2px solid #d1d5db; background: rgba(255,255,255,.8);
        display: flex; align-items: center; justify-content: center; font-size: .625rem;
    }
    .tgx-fp-check.checked { background: var(--p-primary-color, #3b82f6); border-color: var(--p-primary-color, #3b82f6); color: #fff; }
    .tgx-fp-item-label { font-size: .625rem; padding: .25rem .4rem; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tgx-fp-pag { display: flex; align-items: center; justify-content: space-between; padding: .5rem 0; margin-top: .5rem; border-top: 1px solid var(--p-content-border-color, #e5e7eb); font-size: .875rem; }
    .tgx-fp-pag-btns { display: flex; gap: .25rem; align-items: center; }
    .tgx-fp-pag-btn { padding: .25rem .5rem; border: 1px solid var(--p-content-border-color, #e5e7eb); border-radius: var(--p-border-radius-md, .375rem); background: none; cursor: pointer; }
    .tgx-fp-pag-btn:disabled { opacity: .4; cursor: not-allowed; }
    .tgx-fp-detail {
        width: 18rem; flex-shrink: 0; border-left: 1px solid var(--p-content-border-color, #e5e7eb);
        padding: .75rem; overflow-y: auto; display: flex; flex-direction: column; gap: .75rem;
    }
    .tgx-fp-detail-preview img { width: 100%; aspect-ratio: 16/9; object-fit: contain; border-radius: var(--p-border-radius-md, .375rem); background: var(--p-content-hover-background, #f9fafb); }
    .tgx-fp-detail-label { display: block; font-size: .75rem; font-weight: 500; color: var(--p-text-muted-color, #6b7280); margin-bottom: .25rem; }
    .tgx-fp-detail-input {
        width: 100%; padding: .35rem .6rem; border: 1px solid var(--p-content-border-color, #e5e7eb);
        border-radius: var(--p-border-radius-md, .375rem); background: var(--p-content-background, #fff);
        font-size: .875rem; outline: none; color: inherit;
    }
    .tgx-fp-detail-select {
        width: 100%; padding: .35rem .6rem; border: 1px solid var(--p-content-border-color, #e5e7eb);
        border-radius: var(--p-border-radius-md, .375rem); background: var(--p-content-background, #fff);
        font-size: .875rem; outline: none; color: inherit;
    }
    .tgx-fp-browse-inner { flex: 1; overflow: hidden; display: flex; gap: 0; }
    .tgx-fp-browse-grid-pane { flex: 1; overflow: hidden; display: flex; flex-direction: column; }
    .tgx-fp-loading { text-align: center; padding: 2rem; color: var(--p-text-muted-color, #6b7280); }
    .tgx-fp-empty { text-align: center; padding: 2rem; color: var(--p-text-muted-color, #6b7280); }
    .tgx-fp-spinner { display: inline-block; width: 1.5rem; height: 1.5rem; border: 2px solid rgba(0,0,0,.1); border-left-color: var(--p-primary-color, #3b82f6); border-radius: 50%; animation: tgxSpin 1s linear infinite; }
    @keyframes tgxSpin { to { transform: rotate(360deg); } }
    .tgx-fp-footer {
        padding: .75rem 1rem; border-top: 1px solid var(--p-content-border-color, #e5e7eb);
        display: flex; justify-content: space-between; align-items: center;
    }
    .tgx-fp-footer-info { font-size: .875rem; color: var(--p-text-muted-color, #6b7280); }
    .tgx-fp-footer-actions { display: flex; gap: .5rem; }
    .tgx-fp-btn { padding: .4rem .875rem; border-radius: var(--p-border-radius-md, .375rem); font-size: .875rem; cursor: pointer; border: 1px solid transparent; }
    .tgx-fp-btn-secondary { background: none; border-color: var(--p-content-border-color, #e5e7eb); }
    .tgx-fp-btn-secondary:hover { background: var(--p-content-hover-background, #f9fafb); }
    .tgx-fp-btn-primary { background: var(--p-primary-color, #3b82f6); color: #fff; }
    .tgx-fp-btn-primary:hover { opacity: .9; }
    .tgx-fp-btn-primary:disabled { opacity: .4; cursor: not-allowed; }
    .tgx-fp-btn-danger { background: #ef4444; color: #fff; }
    .tgx-fp-dropzone {
        border: 2px dashed var(--p-content-border-color, #e5e7eb); border-radius: var(--p-border-radius-md, .375rem);
        padding: 2rem; text-align: center; cursor: pointer;
        color: var(--p-text-muted-color, #6b7280); transition: border-color .15s, background .15s;
        /* Flex column centering: Filament's preflight makes <svg> block-level,
           so text-align alone leaves the upload icon stuck to the left. */
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        min-height: 10rem;
    }
    .tgx-fp-dropzone.over { border-color: var(--p-primary-color, #3b82f6); background: color-mix(in sRGB, var(--p-primary-color, #3b82f6) 5%, transparent); }
    .tgx-fp-queue { margin-top: .75rem; display: flex; flex-direction: column; gap: .35rem; max-height: 10rem; overflow-y: auto; }
    .tgx-fp-queue-item { display: flex; align-items: center; gap: .75rem; padding: .4rem .75rem; background: var(--p-content-hover-background, #f9fafb); border-radius: var(--p-border-radius-md, .375rem); font-size: .875rem; }
    .tgx-fp-queue-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tgx-fp-queue-remove { background: none; border: none; cursor: pointer; color: var(--p-text-muted-color, #6b7280); padding: .1rem; }
    .tgx-fp-ext-input { width: 100%; padding: .5rem .75rem; border: 1px solid var(--p-content-border-color, #e5e7eb); border-radius: var(--p-border-radius-md, .375rem); background: var(--p-content-background, #fff); font-size: .875rem; outline: none; color: inherit; }
    .tgx-fp-ext-input:focus { border-color: var(--p-primary-color, #3b82f6); }
    .tgx-fp-ext-preview { margin-top: .75rem; border-radius: var(--p-border-radius-md, .375rem); overflow: hidden; border: 1px solid var(--p-content-border-color, #e5e7eb); }
    .tgx-fp-ext-preview img { max-width: 100%; max-height: 200px; object-fit: contain; display: block; }
    /* Field preview area */
    .tgx-fp-field-empty {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .5rem; padding: 1.5rem; border: 2px dashed var(--p-content-border-color, #e5e7eb);
        border-radius: var(--p-border-radius-md, .375rem); cursor: pointer; color: var(--p-text-muted-color, #6b7280);
        transition: border-color .15s, background .15s;
    }
    .tgx-fp-field-empty:hover { border-color: var(--p-primary-color, #3b82f6); background: color-mix(in sRGB, var(--p-primary-color, #3b82f6) 5%, transparent); }
    .tgx-fp-field-preview { position: relative; cursor: pointer; }
    .tgx-fp-field-preview:hover .tgx-fp-overlay-btns { opacity: 1; }
    .tgx-fp-field-img { width: 100%; aspect-ratio: 16/9; object-fit: cover; border-radius: var(--p-border-radius-md, .375rem); display: block; }
    .tgx-fp-overlay-btns { position: absolute; inset: 0; background: rgba(0,0,0,.45); opacity: 0; display: flex; align-items: center; justify-content: center; gap: .5rem; border-radius: var(--p-border-radius-md, .375rem); transition: opacity .15s; }
    .tgx-fp-ovr-btn { width: 2rem; height: 2rem; border-radius: 50%; border: none; cursor: pointer; background: #fff; display: flex; align-items: center; justify-content: center; }
    .tgx-fp-field-filename { margin-top: .25rem; font-size: .75rem; color: var(--p-text-muted-color, #6b7280); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tgx-fp-multi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .tgx-fp-multi-item { position: relative; aspect-ratio: 1; border-radius: var(--p-border-radius-md, .375rem); overflow: hidden; background: var(--p-content-hover-background, #f9fafb); }
    .tgx-fp-multi-item img { width: 100%; height: 100%; object-fit: cover; }
    .tgx-fp-multi-remove { position: absolute; top: .25rem; right: .25rem; background: #ef4444; color: #fff; border: none; border-radius: 50%; width: 1.25rem; height: 1.25rem; font-size: .625rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .tgx-fp-add-tile { aspect-ratio: 1; border: 2px dashed var(--p-content-border-color, #e5e7eb); border-radius: var(--p-border-radius-md, .375rem); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--p-text-muted-color, #6b7280); font-size: 1.25rem; transition: border-color .15s; }
    .tgx-fp-add-tile:hover { border-color: var(--p-primary-color, #3b82f6); color: var(--p-primary-color, #3b82f6); }
    .tgx-fp-multi-footer { display: flex; justify-content: space-between; align-items: center; margin-top: .5rem; font-size: .75rem; }
    .tgx-fp-remove-all { background: none; border: none; cursor: pointer; color: #ef4444; font-size: .75rem; }
</style>
@endonce

@once
<script>
if (!window.tgxMediaGalleryPicker) {
    window.tgxMediaGalleryPicker = function(config) {
        const { multiple, maxFiles, acceptedTypes, disabled } = config;

        return {
            open: false,
            tab: 'browse',
            search: '',
            filterType: null,
            filterFolder: null,
            viewMode: 'grid',
            items: [],
            folders: [],
            pagination: { currentPage: 1, lastPage: 1, total: 0 },
            loading: false,
            uploading: false,
            selectedIds: [],
            focused: null,
            editMode: false,
            editForm: { title: '', alt_text: '', description: '' },
            uploadFiles: [],
            dragOver: false,
            externalUrl: '',
            externalUrlError: false,
            variants: [],
            selectedVariants: {},
            _searchTimer: null,

            init() {
                this.$watch('filterType', () => { if (this.open) this.fetchMedia(1); });
                this.$watch('filterFolder', () => { if (this.open) this.fetchMedia(1); });
            },

            displayImages() {
                if (!this.state) return [];
                return multiple ? (Array.isArray(this.state) ? this.state : []) : [this.state];
            },

            hasValue() {
                if (!this.state) return false;
                return multiple ? (Array.isArray(this.state) && this.state.length > 0) : true;
            },

            canSelectMore() {
                if (!maxFiles) return true;
                return this.selectedIds.length < maxFiles;
            },

            openModal() {
                if (disabled) return;
                const imgs = this.displayImages();
                this.selectedIds = imgs.map(i => i.id).filter(Boolean);
                this.focused = null;
                this.tab = 'browse';
                this.uploadFiles = [];
                this.variants = [];
                this.selectedVariants = {};
                this.externalUrl = '';
                this.externalUrlError = false;
                this.open = true;
                Promise.all([this.fetchMedia(1), this.fetchFolders()]);
            },

            getCsrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            },

            async fetchMedia(page) {
                this.loading = true;
                try {
                    const p = new URLSearchParams({ page: page ?? 1, perPage: 24 });
                    if (this.search) p.set('search', this.search);
                    if (this.filterType) p.set('type', this.filterType);
                    if (this.filterFolder) p.set('folder', this.filterFolder);
                    const r = await fetch(`/tagixo/media?${p}`, { headers: { 'X-CSRF-TOKEN': this.getCsrf(), 'Accept': 'application/json' } });
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    const d = await r.json();
                    this.items = d.data ?? [];
                    const m = d.meta ?? d;
                    this.pagination = { currentPage: m.current_page ?? 1, lastPage: m.last_page ?? 1, total: m.total ?? 0 };
                } catch(e) { console.error('[tgxMediaPicker] fetch', e); }
                finally { this.loading = false; }
            },

            async fetchFolders() {
                try {
                    const r = await fetch('/tagixo/media/folders', { headers: { 'X-CSRF-TOKEN': this.getCsrf(), 'Accept': 'application/json' } });
                    if (!r.ok) return;
                    this.folders = await r.json();
                } catch(e) { console.error('[tgxMediaPicker] folders', e); }
            },

            async fetchVariants(media) {
                if (!media || !media.is_image) { this.variants = []; return; }
                try {
                    const r = await fetch(`/tagixo/media/${media.id}/variants`, { headers: { 'X-CSRF-TOKEN': this.getCsrf(), 'Accept': 'application/json' } });
                    if (!r.ok) return;
                    this.variants = await r.json();
                } catch(e) { this.variants = []; }
            },

            focusItem(media) {
                this.focused = media;
                this.editMode = false;
                this.editForm = { title: media.title ?? '', alt_text: media.alt_text ?? '', description: media.description ?? '' };
                this.fetchVariants(media);
            },

            async saveDetails() {
                if (!this.focused) return;
                try {
                    const r = await fetch(`/tagixo/media/${this.focused.id}`, {
                        method: 'PUT',
                        headers: { 'X-CSRF-TOKEN': this.getCsrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.editForm),
                    });
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    const updated = await r.json();
                    const idx = this.items.findIndex(i => i.id === this.focused.id);
                    if (idx > -1) this.items[idx] = { ...this.items[idx], ...updated };
                    this.focused = { ...this.focused, ...updated };
                    this.editMode = false;
                } catch(e) { console.error('[tgxMediaPicker] save', e); }
            },

            async deleteMedia(media) {
                if (!confirm('Delete this file?')) return;
                try {
                    const r = await fetch(`/tagixo/media/${media.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.getCsrf(), 'Accept': 'application/json' } });
                    if (!r.ok) return;
                    this.items = this.items.filter(i => i.id !== media.id);
                    this.selectedIds = this.selectedIds.filter(id => id !== media.id);
                    if (this.focused?.id === media.id) this.focused = null;
                } catch(e) { console.error('[tgxMediaPicker] delete', e); }
            },

            onSearchInput() {
                clearTimeout(this._searchTimer);
                this._searchTimer = setTimeout(() => this.fetchMedia(1), 300);
            },

            isSelected(media) { return this.selectedIds.includes(media.id); },

            toggleSelect(media) {
                const idx = this.selectedIds.indexOf(media.id);
                if (multiple) {
                    if (idx > -1) this.selectedIds.splice(idx, 1);
                    else if (this.canSelectMore()) this.selectedIds.push(media.id);
                } else {
                    this.selectedIds = idx > -1 ? [] : [media.id];
                }
            },

            buildValue(m) {
                const v = { id: m.id, url: m.url, thumbnail_url: m.thumbnail_url, filename: m.filename, alt_text: m.alt_text ?? '', width: m.width, height: m.height };
                if (m.is_external) v.is_external = true;
                return v;
            },

            applyVariant(m) {
                const key = this.selectedVariants[m.id];
                if (!key || key === 'original') return m;
                const v = this.variants.find(x => x.key === key);
                return v ? { ...m, url: v.url, width: v.width, height: v.height, selected_variant: key } : m;
            },

            confirmSelection() {
                const selected = this.items.filter(m => this.selectedIds.includes(m.id)).map(m => this.applyVariant(m));
                if (!selected.length) { this.open = false; return; }
                this.state = multiple ? selected.map(m => this.buildValue(m)) : this.buildValue(selected[0]);
                this.open = false;
            },

            removeImage(index) {
                if (multiple && index != null) {
                    const next = Array.isArray(this.state) ? [...this.state] : [];
                    next.splice(index, 1);
                    this.state = next;
                } else {
                    this.state = null;
                }
            },

            removeAll() { this.state = []; },

            async doUpload() {
                if (!this.uploadFiles.length) return;
                this.uploading = true;
                try {
                    const fd = new FormData();
                    this.uploadFiles.forEach(f => fd.append('files[]', f));
                    if (this.filterFolder) fd.append('folder', this.filterFolder);
                    const r = await fetch('/tagixo/media/upload', { method: 'POST', headers: { 'X-CSRF-TOKEN': this.getCsrf() }, body: fd });
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    const result = await r.json();
                    this.uploadFiles = [];
                    (result.uploaded ?? []).forEach(m => { if (this.canSelectMore()) this.selectedIds.push(m.id); });
                    this.tab = 'browse';
                    await this.fetchMedia(1);
                } catch(e) { console.error('[tgxMediaPicker] upload', e); alert('Upload failed: ' + e.message); }
                finally { this.uploading = false; }
            },

            onFileDrop(e) {
                e.preventDefault(); this.dragOver = false;
                const files = Array.from(e.dataTransfer?.files ?? []);
                if (acceptedTypes.length) {
                    this.uploadFiles.push(...files.filter(f => acceptedTypes.some(t => t === f.type || (t.endsWith('/*') && f.type.startsWith(t.slice(0,-1))))));
                } else {
                    this.uploadFiles.push(...files);
                }
            },

            confirmExternalUrl() {
                const url = this.externalUrl.trim();
                if (!url) return;
                const filename = url.split('/').pop()?.split('?')[0] || 'external';
                const media = { id: null, url, thumbnail_url: url, filename, alt_text: '', width: null, height: null, is_external: true };
                this.state = multiple ? [...(Array.isArray(this.state) ? this.state : []), media] : media;
                this.open = false;
            },
        };
    };
}
</script>
@endonce

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: @entangle($statePath),
            ...window.tgxMediaGalleryPicker({
                multiple: @js($multiple),
                maxFiles: @js($maxFiles),
                acceptedTypes: @js($acceptedTypes),
                disabled: @js($disabled),
            })
        }"
        x-init="init()"
    >
        {{-- ── Field preview ──────────────────────────────────────────────── --}}
        @if ($multiple)
            <div>
                <div class="tgx-fp-multi-grid">
                    <template x-for="(img, index) in displayImages()" :key="img.id ?? index">
                        <div class="tgx-fp-multi-item">
                            <img :src="img.thumbnail_url ?? img.url" :alt="img.alt_text ?? img.filename" />
                            <button type="button" class="tgx-fp-multi-remove" @click.stop="removeImage(index)" title="Remove">&#10005;</button>
                        </div>
                    </template>
                    <template x-if="!@js($maxFiles) || displayImages().length < @js($maxFiles ?? 999)">
                        <button type="button" class="tgx-fp-add-tile" @click="openModal()" @if($disabled) disabled @endif>+</button>
                    </template>
                </div>
                <div x-show="displayImages().length > 0" class="tgx-fp-multi-footer">
                    <span x-text="displayImages().length + (@js($maxFiles) ? ' / {{ $maxFiles }}' : '') + ' files'"></span>
                    <button type="button" class="tgx-fp-remove-all" @click="removeAll()">Remove all</button>
                </div>
                <div x-show="displayImages().length === 0" class="tgx-fp-field-empty" @click="openModal()" @if($disabled) style="cursor:default;opacity:.6" @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:1.75rem;height:1.75rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    <span>{{ $placeholder }}</span>
                </div>
            </div>
        @else
            <div>
                <div x-show="!hasValue()" class="tgx-fp-field-empty" @click="openModal()" @if($disabled) style="cursor:default;opacity:.6" @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:1.75rem;height:1.75rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    <span>{{ $placeholder }}</span>
                </div>
                <div x-show="hasValue()" class="tgx-fp-field-preview" @click="openModal()">
                    <template x-if="state && state.url">
                        <img :src="state.thumbnail_url ?? state.url" :alt="state.alt_text ?? state.filename" class="tgx-fp-field-img" />
                    </template>
                    <div class="tgx-fp-overlay-btns">
                        <button type="button" class="tgx-fp-ovr-btn" @click.stop="openModal()" title="Change">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:.875rem;height:.875rem" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                        </button>
                        <button type="button" class="tgx-fp-ovr-btn" @click.stop="removeImage()" title="Remove" style="color:#ef4444">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:.875rem;height:.875rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                    <p x-text="state?.filename" class="tgx-fp-field-filename"></p>
                </div>
            </div>
        @endif

        {{-- ── Modal (teleported to body) ─────────────────────────────────── --}}
        <template x-teleport="body">
            <div x-show="open" class="tgx-fp-overlay" @keydown.escape.window="open = false" style="display:none">
                <div class="tgx-fp-dialog" @click.stop>

                    {{-- Header --}}
                    <div class="tgx-fp-header">
                        <span class="tgx-fp-title">Media Gallery</span>
                        <button type="button" class="tgx-fp-close" @click="open = false" title="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:1.25rem;height:1.25rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>

                    {{-- Tabs --}}
                    <div class="tgx-fp-tabs">
                        <button type="button" class="tgx-fp-tab" :class="{ active: tab === 'browse' }" @click="tab = 'browse'">Browse</button>
                        <button type="button" class="tgx-fp-tab" :class="{ active: tab === 'upload' }" @click="tab = 'upload'">
                            Upload
                            <template x-if="uploadFiles.length > 0">
                                <span x-text="' (' + uploadFiles.length + ')'"></span>
                            </template>
                        </button>
                        <button type="button" class="tgx-fp-tab" :class="{ active: tab === 'external' }" @click="tab = 'external'">External URL</button>
                    </div>

                    {{-- Body --}}
                    <div class="tgx-fp-body">

                        {{-- ── Browse tab ──────────────────────────────── --}}
                        <div x-show="tab === 'browse'" class="tgx-fp-tab-pane">
                            <div class="tgx-fp-toolbar">
                                <input type="text" x-model="search" @input="onSearchInput()" placeholder="Search…" class="tgx-fp-search" />
                                <select x-model="filterType" class="tgx-fp-select">
                                    <option value="">All types</option>
                                    <option value="image">Images</option>
                                    <option value="video">Videos</option>
                                    <option value="document">Documents</option>
                                </select>
                                <template x-if="folders.length > 0">
                                    <select x-model="filterFolder" class="tgx-fp-select">
                                        <option value="">All folders</option>
                                        <template x-for="f in folders" :key="f.name ?? f">
                                            <option :value="f.name ?? f" x-text="f.name ?? f"></option>
                                        </template>
                                    </select>
                                </template>
                                <button x-show="search || filterType || filterFolder" type="button" class="tgx-fp-btn tgx-fp-btn-secondary" @click="search=''; filterType=null; filterFolder=null; fetchMedia(1)">Reset</button>
                                <div class="tgx-fp-view-toggle">
                                    <button type="button" class="tgx-fp-view-btn" :class="{ active: viewMode==='grid' }" @click="viewMode='grid'" title="Grid">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width:.875rem;height:.875rem" viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    </button>
                                    <button type="button" class="tgx-fp-view-btn" :class="{ active: viewMode==='list' }" @click="viewMode='list'" title="List">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width:.875rem;height:.875rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 000 2h14a1 1 0 100-2H3zm0 4a1 1 0 000 2h14a1 1 0 100-2H3zm0 4a1 1 0 000 2h14a1 1 0 100-2H3z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                                <span x-show="selectedIds.length > 0" style="font-size:.875rem;color:var(--p-text-muted-color,#6b7280)">
                                    <span x-text="selectedIds.length"></span> selected
                                    <button type="button" style="background:none;border:none;cursor:pointer;color:var(--p-primary-color,#3b82f6);font-size:.875rem;text-decoration:underline" @click="selectedIds=[]">Deselect</button>
                                </span>
                            </div>

                            <div class="tgx-fp-browse-inner">
                                <div class="tgx-fp-browse-grid-pane">
                                    <div x-show="loading" class="tgx-fp-loading"><div class="tgx-fp-spinner"></div></div>
                                    <div x-show="!loading && items.length === 0" class="tgx-fp-empty">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width:2rem;height:2rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                                        <p>No media found</p>
                                    </div>
                                    <div class="tgx-fp-items-wrap" x-show="!loading && items.length > 0">
                                        {{-- Grid view --}}
                                        <div x-show="viewMode === 'grid'" class="tgx-fp-grid">
                                            <template x-for="item in items" :key="item.id">
                                                <div class="tgx-fp-item tgx-fp-item-grid" :class="{ selected: isSelected(item) }" @click="focusItem(item); toggleSelect(item)">
                                                    <div class="tgx-fp-thumb">
                                                        <img x-show="item.type === 'image'" :src="item.thumbnail_url ?? item.url" :alt="item.filename" loading="lazy" />
                                                        <div x-show="item.type !== 'image'" class="tgx-fp-thumb-icon">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width:1.5rem;height:1.5rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586V3h4a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                    </div>
                                                    @if ($multiple)
                                                    <div class="tgx-fp-check" :class="{ checked: isSelected(item) }" @click.stop="toggleSelect(item)">
                                                        <template x-if="isSelected(item)">&#10003;</template>
                                                    </div>
                                                    @endif
                                                    <p class="tgx-fp-item-label" x-text="item.filename"></p>
                                                </div>
                                            </template>
                                        </div>
                                        {{-- List view --}}
                                        <div x-show="viewMode === 'list'" class="tgx-fp-list-view">
                                            <template x-for="item in items" :key="item.id">
                                                <div class="tgx-fp-item tgx-fp-item-list" :class="{ selected: isSelected(item) }" @click="focusItem(item); toggleSelect(item)">
                                                    @if ($multiple)
                                                    <div class="tgx-fp-check" :class="{ checked: isSelected(item) }" @click.stop="toggleSelect(item)">
                                                        <template x-if="isSelected(item)">&#10003;</template>
                                                    </div>
                                                    @endif
                                                    <div style="width:3rem;height:3rem;flex-shrink:0;border-radius:var(--p-border-radius-md,.375rem);overflow:hidden;background:var(--p-content-hover-background,#f9fafb)">
                                                        <img x-show="item.type === 'image'" :src="item.thumbnail_url ?? item.url" :alt="item.filename" style="width:100%;height:100%;object-fit:cover" />
                                                        <div x-show="item.type !== 'image'" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width:1.25rem;height:1.25rem;color:var(--p-text-muted-color,#6b7280)" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586V3h4a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                    </div>
                                                    <div style="flex:1;min-width:0">
                                                        <p style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.875rem" x-text="item.filename"></p>
                                                        <p style="font-size:.75rem;color:var(--p-text-muted-color,#6b7280)" x-text="item.formatted_size + (item.width && item.height ? ' · ' + item.width + '×' + item.height : '')"></p>
                                                    </div>
                                                    <span style="padding:.2rem .5rem;border-radius:.25rem;font-size:.75rem;background:var(--p-content-hover-background,#f9fafb)" x-text="item.type"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    {{-- Pagination --}}
                                    <div x-show="!loading && pagination.lastPage > 1" class="tgx-fp-pag">
                                        <span style="font-size:.875rem;color:var(--p-text-muted-color,#6b7280)" x-text="pagination.total + ' results'"></span>
                                        <div class="tgx-fp-pag-btns">
                                            <button type="button" class="tgx-fp-pag-btn" :disabled="pagination.currentPage <= 1" @click="fetchMedia(pagination.currentPage - 1)">&#8249;</button>
                                            <span style="font-size:.875rem;padding:0 .5rem" x-text="pagination.currentPage + ' / ' + pagination.lastPage"></span>
                                            <button type="button" class="tgx-fp-pag-btn" :disabled="pagination.currentPage >= pagination.lastPage" @click="fetchMedia(pagination.currentPage + 1)">&#8250;</button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Detail panel --}}
                                <div x-show="focused" class="tgx-fp-detail">
                                    <div class="tgx-fp-detail-preview">
                                        <template x-if="focused && focused.type === 'image'">
                                            <img :src="focused.url" :alt="focused.filename" />
                                        </template>
                                        <template x-if="focused && focused.type !== 'image'">
                                            <div style="width:100%;aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;background:var(--p-content-hover-background,#f9fafb);border-radius:var(--p-border-radius-md,.375rem)">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:3rem;height:3rem;color:var(--p-text-muted-color,#6b7280)" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586V3h4a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="!editMode">
                                        <div><label class="tgx-fp-detail-label">Filename</label><p style="font-size:.875rem;word-break:break-all" x-text="focused?.filename"></p></div>
                                        <template x-if="focused?.title"><div><label class="tgx-fp-detail-label">Title</label><p style="font-size:.875rem" x-text="focused.title"></p></div></template>
                                        <template x-if="focused?.alt_text"><div><label class="tgx-fp-detail-label">Alt Text</label><p style="font-size:.875rem" x-text="focused.alt_text"></p></div></template>
                                        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem">
                                            <button type="button" class="tgx-fp-btn tgx-fp-btn-secondary" @click="editMode = true">Edit</button>
                                            <button type="button" class="tgx-fp-btn tgx-fp-btn-danger" @click="deleteMedia(focused)">Delete</button>
                                        </div>
                                    </div>

                                    <div x-show="editMode">
                                        <div><label class="tgx-fp-detail-label">Title</label><input type="text" x-model="editForm.title" class="tgx-fp-detail-input" /></div>
                                        <div style="margin-top:.5rem"><label class="tgx-fp-detail-label">Alt Text</label><input type="text" x-model="editForm.alt_text" class="tgx-fp-detail-input" /></div>
                                        <div style="margin-top:.5rem"><label class="tgx-fp-detail-label">Description</label><textarea x-model="editForm.description" class="tgx-fp-detail-input" rows="2"></textarea></div>
                                        <div style="display:flex;gap:.5rem;margin-top:.5rem">
                                            <button type="button" class="tgx-fp-btn tgx-fp-btn-primary" @click="saveDetails()">Save</button>
                                            <button type="button" class="tgx-fp-btn tgx-fp-btn-secondary" @click="editMode = false">Cancel</button>
                                        </div>
                                    </div>

                                    <template x-if="variants.length > 1">
                                        <div>
                                            <label class="tgx-fp-detail-label">Image size</label>
                                            <select x-model="selectedVariants[focused?.id]" class="tgx-fp-detail-select">
                                                <template x-for="v in variants" :key="v.key">
                                                    <option :value="v.key" x-text="v.label + ' — ' + v.formatted_size"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>

                                    <div>
                                        <button type="button" class="tgx-fp-btn" style="width:100%" :class="isSelected(focused) ? 'tgx-fp-btn-secondary' : 'tgx-fp-btn-primary'" @click="toggleSelect(focused)">
                                            <span x-text="isSelected(focused) ? 'Deselect' : 'Select'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Upload tab ──────────────────────────────── --}}
                        <div x-show="tab === 'upload'" class="tgx-fp-tab-pane">
                            <div class="tgx-fp-dropzone" :class="{ over: dragOver }"
                                @dragover.prevent="dragOver = true"
                                @dragleave.prevent="dragOver = false"
                                @drop.prevent="onFileDrop($event)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:2.5rem;height:2.5rem;margin-bottom:.5rem" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <p>Drag files here or</p>
                                <input x-ref="fileInput" type="file" @if($multiple) multiple @endif style="display:none" @change="uploadFiles.push(...Array.from($refs.fileInput.files)); $refs.fileInput.value=''" />
                                <button type="button" class="tgx-fp-btn tgx-fp-btn-secondary" style="margin-top:.5rem" @click="$refs.fileInput.click()">Select files</button>
                            </div>
                            <div x-show="uploadFiles.length > 0">
                                <h4 style="font-size:.875rem;font-weight:600;margin:.75rem 0 .5rem">Files to upload (<span x-text="uploadFiles.length"></span>)</h4>
                                <div class="tgx-fp-queue">
                                    <template x-for="(f, i) in uploadFiles" :key="i">
                                        <div class="tgx-fp-queue-item">
                                            <span class="tgx-fp-queue-name" x-text="f.name"></span>
                                            <button type="button" class="tgx-fp-queue-remove" @click="uploadFiles.splice(i,1)">&#10005;</button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="tgx-fp-btn tgx-fp-btn-primary" style="margin-top:.75rem;width:100%" :disabled="uploading" @click="doUpload()">
                                    <span x-show="!uploading">Upload</span>
                                    <span x-show="uploading">Uploading…</span>
                                </button>
                            </div>
                        </div>

                        {{-- ── External URL tab ─────────────────────────── --}}
                        <div x-show="tab === 'external'" class="tgx-fp-tab-pane" style="align-items:center;justify-content:center">
                            <div style="width:100%;max-width:32rem;display:flex;flex-direction:column;gap:1rem">
                                <div style="text-align:center;color:var(--p-text-muted-color,#6b7280)">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;display:block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                                    <p>Enter the URL of an external image or video</p>
                                </div>
                                <div>
                                    <label class="tgx-fp-detail-label">URL</label>
                                    <input type="url" x-model="externalUrl" placeholder="https://example.com/image.jpg" class="tgx-fp-ext-input" @keyup.enter="confirmExternalUrl()" />
                                </div>
                                <template x-if="externalUrl.trim()">
                                    <div class="tgx-fp-ext-preview">
                                        <img :src="externalUrl.trim()" style="max-width:100%;max-height:200px;object-fit:contain;display:block" x-on:error="externalUrlError=true" x-on:load="externalUrlError=false" />
                                    </div>
                                </template>
                                <p x-show="externalUrlError" style="color:#ef4444;font-size:.875rem">Unable to load preview — check the URL.</p>
                                <button type="button" class="tgx-fp-btn tgx-fp-btn-primary" :disabled="!externalUrl.trim()" @click="confirmExternalUrl()">Use this URL</button>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="tgx-fp-footer">
                        <p class="tgx-fp-footer-info">
                            <span x-text="selectedIds.length"></span> files selected
                            <template x-if="@js($maxFiles)"><span> (max {{ $maxFiles }})</span></template>
                        </p>
                        <div class="tgx-fp-footer-actions">
                            <button type="button" class="tgx-fp-btn tgx-fp-btn-secondary" @click="open = false">Cancel</button>
                            <button type="button" class="tgx-fp-btn tgx-fp-btn-primary" :disabled="selectedIds.length === 0" @click="confirmSelection()">Confirm</button>
                        </div>
                    </div>

                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
