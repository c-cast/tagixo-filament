<?php

namespace Tagixo\Filament\Resources;

/**
 * Templates: a header and a footer are documents of their own,
 * which the Theme Builder opens one zone at a time.
 */
class LayoutResource extends TagixoRecordResource
{
    protected static string $tagixoType = 'layouts';
}
