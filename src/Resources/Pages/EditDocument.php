<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\DocumentResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is EditTagixoRecord's.
 */
class EditDocument extends EditTagixoRecord
{
    protected static string $resource = DocumentResource::class;
}
