<?php

use Tagixo\Core\ComponentRegistry;
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
