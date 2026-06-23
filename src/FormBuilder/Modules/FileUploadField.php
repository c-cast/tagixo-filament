<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Arr;

final class FileUploadField implements FilamentFieldModule
{
    use AppliesFilamentFieldConfig;

    public static function toFilamentField(
        array $field,
        BuilderModelRegistryService $modelRegistry,
        ?Closure $modelOptionsResolver = null,
    ): ?Component {
        $name = self::resolveStatePath($field);
        if ($name === null) {
            return null;
        }

        $component = FileUpload::make($name);

        $accept = (string) ($field['accept'] ?? '');
        if ($accept !== '') {
            $acceptedTypes = self::splitCsv($accept);
            if (! empty($acceptedTypes)) {
                $component->acceptedFileTypes($acceptedTypes);
            }
        }

        if (! empty($field['multiple'])) {
            $component->multiple();
        }

        $maxSize = Arr::get($field, 'validation.max_file_size', $field['max_size'] ?? null);
        if (is_numeric($maxSize)) {
            $component->maxSize((int) $maxSize);
        }

        $minFiles = Arr::get($field, 'validation.min_files');
        if (is_numeric($minFiles)) {
            $component->minFiles((int) $minFiles);
        }

        $maxFiles = Arr::get($field, 'validation.max_files');
        if (is_numeric($maxFiles)) {
            $component->maxFiles((int) $maxFiles);
        }

        $mimes = Arr::get($field, 'validation.mimes');
        if (is_string($mimes) && trim($mimes) !== '') {
            $component->rule('mimes:'.$mimes);
        }

        return self::applyCommonFieldConfig($component, $field);
    }
}
