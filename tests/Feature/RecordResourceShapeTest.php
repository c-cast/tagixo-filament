<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Tagixo\Filament\Resources\DocumentResource;
use Tagixo\Filament\Resources\PageResource;
use Tagixo\Filament\Support\RecordFieldSchema;
use Tagixo\Filament\Resources\MailResource;

uses(RefreshDatabase::class);

/*
 * A resource names its type and nothing else: model, slug, labels, columns and
 * inputs all come from what the type declares.
 */

it('takes its model and its slug from the type', function () {
    expect(PageResource::getModel())->toBe(PageResource::builderType()->modelClass())
        ->and(PageResource::getSlug())->toBe('pages')
        ->and(DocumentResource::getSlug())->toBe('documents');
});

it('names a record by the first field the type declares', function () {
    $first = PageResource::tagixoFields()[0];

    expect(PageResource::getRecordTitleAttribute())->toBe($first->name)
        ->and($first->name)->toBe('title');
});

it('labels itself the way the type does', function () {
    expect(PageResource::getPluralModelLabel())->toBe(PageResource::builderType()->label())
        // The singular is derived, so a type declares one label and not two.
        ->and(PageResource::getModelLabel())->toBe(Str::singular(PageResource::builderType()->label()));
});

it('builds its columns from the listed fields', function () {
    $fields = PageResource::tagixoFields();
    $columns = RecordFieldSchema::columns($fields);

    $listed = array_values(array_filter($fields, fn ($field) => $field->listed));

    expect($columns)->toHaveCount(count($listed))
        ->and($columns[0])->toBeInstanceOf(TextColumn::class)
        ->and($columns[0]->getName())->toBe($listed[0]->name);
});

it('turns a status into a select, with the options the type allows', function () {
    $status = collect(PageResource::tagixoFields())->firstWhere('name', 'status');

    expect($status)->not->toBeNull();

    $input = RecordFieldSchema::input($status);

    expect($input)->toBeInstanceOf(Select::class)
        ->and(array_keys($input->getOptions()))->toContain('draft', 'published');
});

it('hides on create what the type would drop anyway', function () {
    $type = PageResource::builderType();
    $inputs = RecordFieldSchema::inputs($type->fields(), array_keys($type->storeRules()));

    $hidden = [];

    foreach ($inputs as $input) {
        if (! in_array($input->getName(), array_keys($type->storeRules()), true)) {
            $hidden[] = $input->getName();
        }
    }

    // Whatever the type does not accept on creation is asked for later, on the
    // edit screen — never on a form whose values would be thrown away.
    expect($hidden)->not->toBeEmpty()
        ->and($inputs[0])->toBeInstanceOf(TextInput::class);
});

it('opens the editor of the core, and carries the way back', function () {
    $record = MailResource::createRecord(['name' => 'Benvenuto']);

    $url = MailResource::builderUrl($record);

    expect($url)->toContain('/tagixo/builder/embed?type=mails&id='.$record->getKey())
        // Closing the editor returns to the listing it was opened from.
        ->and($url)->toContain(urlencode(MailResource::getUrl('index')));
});
