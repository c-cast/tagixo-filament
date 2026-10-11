<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\LayoutResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is CreateTagixoRecord's.
 */
class CreateLayout extends CreateTagixoRecord
{
    protected static string $resource = LayoutResource::class;
}
