<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\FormBuilder\Concerns\AppliesFilamentFieldConfig;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Arr;

final class DatePickerField implements FilamentFieldModule
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

        $enableTime = (bool) ($field['enable_time'] ?? false);

        $component = $enableTime
            ? DateTimePicker::make($name)->seconds(false)
            : DatePicker::make($name);

        $dateFormat = Arr::get($field, 'validation.date_format');
        if (is_string($dateFormat) && trim($dateFormat) !== '') {
            $component->format($dateFormat);
        }

        $beforeDate = Arr::get($field, 'validation.before_date');
        if (is_string($beforeDate) && trim($beforeDate) !== '') {
            $component->rule('before:'.$beforeDate);
        }

        $afterDate = Arr::get($field, 'validation.after_date');
        if (is_string($afterDate) && trim($afterDate) !== '') {
            $component->rule('after:'.$afterDate);
        }

        return self::applyCommonFieldConfig($component, $field);
    }
}
