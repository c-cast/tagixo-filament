<?php

namespace Ccast\TagixoFilament\Concerns;

trait CleansBuilderStructure
{
    /**
     * Recursively remove null values, empty strings, and empty arrays from the structure.
     * Also removes objects that contain only default-only keys.
     */
    protected function cleanStructure(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        $cleaned = [];

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $value = $this->cleanStructure($value);

                if ($value === []) {
                    continue;
                }
            }

            $cleaned[$key] = $value;
        }

        // Preserve sequential arrays (lists of components, items, etc.)
        if ($this->isSequentialArray($data) && ! empty($cleaned)) {
            return array_values($cleaned);
        }

        // Remove objects that only contain default-only keys
        if ($this->isDefaultOnly($cleaned)) {
            return [];
        }

        return $cleaned;
    }

    /**
     * Check if an associative array contains only keys with known default values.
     */
    protected function isDefaultOnly(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        $defaultOnlyKeys = ['font_size_unit' => 'px'];

        foreach ($arr as $key => $value) {
            if (! isset($defaultOnlyKeys[$key]) || $defaultOnlyKeys[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an array is sequential (0-indexed list) vs associative (object).
     */
    protected function isSequentialArray(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
