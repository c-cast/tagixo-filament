<?php

namespace Tagixo\Filament\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * The records of one builder type. Columns and query belong to the type, so
 * this page is the Filament default plus the one action that makes sense here.
 */
class ListTagixoRecords extends ListRecords
{
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
