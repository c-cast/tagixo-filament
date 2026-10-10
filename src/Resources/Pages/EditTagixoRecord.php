<?php

namespace Tagixo\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Tagixo\Filament\Resources\TagixoRecordResource;

/**
 * Metadata of a builder record. The structure is edited in the builder, which
 * this page links to; saving goes through the type, whose rules carry what only
 * it knows — a unique slug, the statuses it allows.
 */
class EditTagixoRecord extends EditRecord
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var class-string<TagixoRecordResource> $resource */
        $resource = static::getResource();

        return $resource::dataForForm($this->getRecord(), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var class-string<TagixoRecordResource> $resource */
        $resource = static::getResource();

        return $resource::updateRecord($record, $data);
    }

    protected function getHeaderActions(): array
    {
        /** @var class-string<TagixoRecordResource> $resource */
        $resource = static::getResource();

        return array_values(array_filter([
            Action::make('build')
                ->label(__('Build'))
                ->icon('heroicon-o-pencil-square')
                ->url($resource::builderUrl($this->getRecord())),
            $resource::previewAction()?->record($this->getRecord()),
            DeleteAction::make(),
        ]));
    }
}
