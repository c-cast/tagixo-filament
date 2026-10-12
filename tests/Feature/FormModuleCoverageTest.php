<?php

use Tagixo\Core\ComponentRegistry;
use Tagixo\Core\Services\BuilderApiService;
use Tagixo\Filament\FormBuilder\FilamentModuleRegistry;
use Tagixo\Filament\TagixoFilamentServiceProvider;

/*
 * A form module speaks two vocabularies: its module id in the builder
 * (`text-input`, `form-grid`) and its field type in a saved schema (`text`,
 * `grid`). This registry is keyed by the second, because that is what the
 * mapper reads — and a key that matches nothing drops the field from the
 * rendered form without a word.
 */

it('has a Filament counterpart for every form module, by field type', function () {
    $registry = app(ComponentRegistry::class);

    $missing = [];

    foreach ($registry->forContext('form')->keys() as $id) {
        $metadata = $registry->getMetadata($id) ?? [];
        $fieldType = $metadata['field_type'] ?? null;

        if ($fieldType === null) {
            // Not a form module: the canvas root, or a page's way of embedding
            // a form rather than something inside one.
            continue;
        }

        $covered = ($metadata['is_wrapper'] ?? false)
            ? array_key_exists($fieldType, TagixoFilamentServiceProvider::WRAPPERS)
            : array_key_exists($fieldType, TagixoFilamentServiceProvider::FIELDS);

        if (! $covered) {
            $missing[] = "{$id} ({$fieldType})";
        }
    }

    expect($missing)->toBe([]);
});

it('knows which modules hold other fields', function () {
    $registry = app(ComponentRegistry::class);

    $grid = $registry->getMetadata('form-grid') ?? [];
    $text = $registry->getMetadata('text-input') ?? [];

    // The registry publishes what the module declares; without this the mapper
    // treats a layout as a field and the layout disappears.
    expect($grid['field_type'])->toBe('grid')
        ->and($grid['is_wrapper'])->toBeTrue()
        ->and($text['field_type'])->toBe('text')
        ->and($text['is_wrapper'])->toBeFalse();
});

it('maps every key it declares to a module that exists', function () {
    $registry = app(FilamentModuleRegistry::class);

    foreach (array_keys(TagixoFilamentServiceProvider::FIELDS) as $type) {
        expect($registry->getField($type))->not->toBeNull("campo mancante per {$type}");
    }

    foreach (array_keys(TagixoFilamentServiceProvider::WRAPPERS) as $type) {
        expect($registry->getWrapper($type))->not->toBeNull("wrapper mancante per {$type}");
    }
});

/*
 * The modules above are the form builder's own. This SDK also brings builder
 * modules of its own, registered when the form-builder capability is applied —
 * so nothing here exercised them, and one was calling a method the core had
 * dropped. Defining them is what makes the editor list them at all.
 */

it('can define every builder module it brings itself', function () {
    $registry = app(ComponentRegistry::class);

    foreach (sdkFormModules() as $module) {
        $registry->register($module);
    }

    // The editor's own call: this is what resolves every tab, prop type and
    // meta of every module, and the only thing that runs define() at all.
    $components = collect(app(BuilderApiService::class)->getAvailableComponents('form'))
        ->keyBy(fn (array $component): string => $component['type']);

    foreach (sdkFormModules() as $module) {
        $component = $components->get($module::getTypeId());

        expect($component)->not->toBeNull("{$module} non arriva all'editor del form");

        $metadata = $registry->getMetadata($module::getTypeId()) ?? [];
        $fieldType = $metadata['field_type'] ?? null;

        expect($fieldType)->toBe($module::getFieldType(), "{$module} non pubblica il suo tipo di campo")
            ->and($component['meta']['fieldType'] ?? null)
            ->toBe($fieldType, "{$module} non dichiara il tipo di campo nel suo meta");

        $covered = ($metadata['is_wrapper'] ?? false)
            ? array_key_exists($fieldType, TagixoFilamentServiceProvider::WRAPPERS)
            : array_key_exists($fieldType, TagixoFilamentServiceProvider::FIELDS);

        expect($covered)->toBeTrue("{$module} non ha una controparte Filament per {$fieldType}");
    }
});

it('brings at least the toggle, so the test above has something to chew on', function () {
    expect(sdkFormModules())->toContain(Tagixo\Filament\FormBuilder\Modules\ToggleModule::class);
});

/**
 * The builder modules this SDK ships, read off the directory rather than a
 * list, so a new one is guarded the day it is written.
 *
 * @return list<class-string<\Tagixo\FormBuilder\FormModule>>
 */
function sdkFormModules(): array
{
    $modules = [];

    foreach (glob(__DIR__.'/../../src/FormBuilder/Modules/*.php') ?: [] as $file) {
        $class = 'Tagixo\\Filament\\FormBuilder\\Modules\\'.basename($file, '.php');

        if (class_exists($class) && is_subclass_of($class, \Tagixo\FormBuilder\FormModule::class)) {
            $modules[] = $class;
        }
    }

    return $modules;
}
