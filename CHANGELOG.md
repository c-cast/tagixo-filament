# Changelog

All notable changes to `ccast/tagixo-filament` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.0.24] - 2026-07-10

### Added

- **Pages list: "Pages" / "Model templates" tabs** — source-synced template pages were invisible before, excluded by the `userManaged` scope; the tabs expose both sets.
- **Theme Builder: lazy model pages** — the Body button of model-scoped templates lazily creates the special page via `Tagixo::ensureRoutePagesForModel` and opens that page's builder (`model_archive` → archive, `model_all` / `model_taxonomy` / `model_record` → single). The button state is driven by `isBodyConfigured`, which checks the special page's content using the pure `findRoutePagesForModel` lookup.

### Fixed

- `media-gallery-picker.blade`: the Alpine `@error=` attribute was parsed as a Blade directive (unclosed `@error`), breaking compilation of the entire form — replaced with `x-on:error`.
- Upload dropzone content is now centred with a flex column — Filament's preflight makes `<svg>` elements block-level, so `text-align` alone was not enough.
- `MediaGalleryPickerField` now uses the `HasPlaceholder` concern, fixing `Undefined variable $getPlaceholder` in the view.
