<?php

namespace Ccast\TagixoFilament\Filament\Resources\Pages\Pages;

use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure content structure exists
        if (! isset($data['content'])) {
            $data['content'] = [
                'sections' => [],
            ];
        }

        return $data;
    }
}
