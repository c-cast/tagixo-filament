<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\PageResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is ListTagixoRecords's.
 */
class ListPages extends ListTagixoRecords
{
    protected static string $resource = PageResource::class;
}
