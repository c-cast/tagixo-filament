<?php

namespace Ccast\TagixoFilament\Filament\Resources\Forms\Pages;

use Ccast\TagixoFilament\Filament\Forms\Form;
use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

/**
 * Standalone preview of an 'app'-target Tagixo form, rendered as a REAL Filament
 * form (native interactive Tabs/Wizard) — the SDK previewer Tagixo core delegates
 * to (Tagixo::registerAppFormPreviewer). The saved Tagixo schema is converted to
 * Filament Schema components by the existing Form bridge / FormSchemaToFilamentMapper.
 *
 * Filament v5: a resource page hosting a schema (InteractsWithSchemas). The named
 * schema method `previewForm()` is resolved when `$this->previewForm` is rendered
 * in the view — mirrors FilamentVisualBuilderPage in this package.
 */
class PreviewAppForm extends Page implements HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithSchemas;

    protected static string $resource = FormResource::class;

    protected string $view = 'tagixo-filament::filament.resources.forms.pages.preview-app-form';

    /** Form state path. */
    public ?array $data = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return trim((string) ($this->record?->title ?? 'Form')) . ' — ' . __('Preview');
    }

    /**
     * Build the preview schema from the saved Tagixo form (by id) via the existing
     * Tagixo→Filament mapper bridge. No submit action — preview only.
     */
    public function previewForm(Schema $schema): Schema
    {
        return Form::configure($schema, (int) $this->record->getKey())
            ->statePath('data');
    }
}
