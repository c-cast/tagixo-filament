<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\GlobalBlockResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is CreateTagixoRecord's.
 */
class CreateGlobalBlock extends CreateTagixoRecord
{
    protected static string $resource = GlobalBlockResource::class;
}
