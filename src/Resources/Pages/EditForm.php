<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\FormResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is EditTagixoRecord's.
 */
class EditForm extends EditTagixoRecord
{
    protected static string $resource = FormResource::class;
}
