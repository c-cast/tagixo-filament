<?php

namespace Tagixo\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Tagixo\Filament\Resources\TagixoRecordResource;

/**
 * A new builder record. Creating goes through the type — a page decides its own
 * slug, a document its default sheet — and lands where that type says.
 */
class CreateTagixoRecord extends CreateRecord
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var class-string<TagixoRecordResource> $resource */
        $resource = static::getResource();

        return $resource::createRecord($data);
    }

    protected function getRedirectUrl(): string
    {
        /** @var class-string<TagixoRecordResource> $resource */
        $resource = static::getResource();

        return $resource::afterCreateUrl($this->getRecord());
    }
}
