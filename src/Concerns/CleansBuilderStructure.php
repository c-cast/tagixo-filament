<?php

namespace Ccast\TagixoFilament\Concerns;

use Ccast\Tagixo\Concerns\CleansBuilderStructure as TagixoCleansBuilderStructure;

/**
 * Thin re-export of the plugin's CleansBuilderStructure trait for use within
 * the Filament SDK. Preserves no structural empty-array keys (unlike the builder
 * canvas variant which keeps `props`/`_styles` to preserve panel open state).
 */
trait CleansBuilderStructure
{
    use TagixoCleansBuilderStructure {
        TagixoCleansBuilderStructure::cleanStructure as private baseCleanStructure;
    }

    protected function cleanStructure(mixed $data, bool $preserveNullValues = false): mixed
    {
        $savedKeys = $this->preservedEmptyArrayKeys;
        $this->preservedEmptyArrayKeys = [];
        $result = $this->baseCleanStructure($data, $preserveNullValues);
        $this->preservedEmptyArrayKeys = $savedKeys;

        return $result;
    }
}
