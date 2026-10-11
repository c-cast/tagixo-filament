<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\PopupResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is CreateTagixoRecord's.
 */
class CreatePopup extends CreateTagixoRecord
{
    protected static string $resource = PopupResource::class;
}
