<?php

namespace Tagixo\Filament\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Tagixo\Filament\Resources\TagixoRecordResource;

/**
 * Mails, with a listing URL that does not need the panel's routes: those are
 * registered by a real Filament application, which the sandbox provides.
 */
class MailResourceWithKnownUrl extends TagixoRecordResource
{
    protected static string $tagixoType = 'mails';

    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        return 'https://panel.test/admin/mails';
    }
}
