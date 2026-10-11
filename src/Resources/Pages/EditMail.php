<?php

namespace Tagixo\Filament\Resources\Pages;

use Tagixo\Filament\Resources\MailResource;

/**
 * Filament binds a page to its resource with a static property, so one page
 * class serves one resource. The behaviour is EditTagixoRecord's.
 */
class EditMail extends EditTagixoRecord
{
    protected static string $resource = MailResource::class;
}
