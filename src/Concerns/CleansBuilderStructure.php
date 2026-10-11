<?php

namespace Tagixo\Filament\Concerns;

use Tagixo\Core\Concerns\CleansBuilderStructure as CoreCleansBuilderStructure;

/**
 * The core's CleansBuilderStructure, with the one difference that is the whole
 * reason this exists: it preserves no structural empty-array keys. The canvas
 * variant keeps the props and styles buckets so the panel remembers which
 * groups were open; a payload leaving the SDK has no panel to remember for.
 */
trait CleansBuilderStructure
{
    use CoreCleansBuilderStructure {
        CoreCleansBuilderStructure::cleanStructure as private baseCleanStructure;
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
