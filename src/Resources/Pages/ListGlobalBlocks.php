<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\GlobalBlockResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is ListTagixoRecords's.
 */
class ListGlobalBlocks extends ListTagixoRecords
{
    protected static string $resource = GlobalBlockResource::class;
}
