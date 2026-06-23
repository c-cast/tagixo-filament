<?php

use Ccast\TagixoFilament\Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('maps body grid columns across all form builder viewports', function () {
    $schema = Schema::make();

    Form::configure($schema, [
        'body' => [
            'grid' => ['columns' => 6],
            '_responsive' => [
                'mobile' => ['grid' => ['columns' => 1]],
                'tablet_portrait' => ['grid' => ['columns' => 2]],
                'tablet_landscape' => ['grid' => ['columns' => 3]],
                'ultrawide' => ['grid' => ['columns' => 8]],
            ],
        ],
        'fields' => [],
    ]);

    expect($schema->getColumns('default'))->toBe(1);
    expect($schema->getColumns('sm'))->toBe(2);
    expect($schema->getColumns('md'))->toBe(3);
    expect($schema->getColumns('lg'))->toBe(6);
    expect($schema->getColumns('xl'))->toBe(8);
});

it('keeps default breakpoint fallback when mobile body viewport is missing', function () {
    $schema = Schema::make();

    Form::configure($schema, [
        'body' => [
            'grid' => ['columns' => 5],
            '_responsive' => [
                'tablet_portrait' => ['grid' => ['columns' => 2]],
                'ultrawide' => ['grid' => ['columns' => 9]],
            ],
        ],
        'fields' => [],
    ]);

    expect($schema->getColumns('default'))->toBe(1);
    expect($schema->getColumns('sm'))->toBe(2);
    expect($schema->getColumns('md'))->toBeNull();
    expect($schema->getColumns('lg'))->toBe(5);
    expect($schema->getColumns('xl'))->toBe(9);
});

it('ignores invalid body viewport values and preserves schema defaults', function () {
    $schema = Schema::make();

    Form::configure($schema, [
        'body' => [
            'grid' => ['columns' => 99],
            '_responsive' => [
                'mobile' => ['grid' => ['columns' => 0]],
                'tablet_portrait' => ['grid' => ['columns' => 15]],
                'tablet_landscape' => ['grid' => ['columns' => 'not-a-number']],
                'ultrawide' => ['grid' => ['columns' => -2]],
            ],
        ],
        'fields' => [],
    ]);

    expect($schema->getColumns('default'))->toBe(1);
    expect($schema->getColumns('sm'))->toBeNull();
    expect($schema->getColumns('md'))->toBeNull();
    expect($schema->getColumns('lg'))->toBeNull();
    expect($schema->getColumns('xl'))->toBeNull();
});

it('maps responsive column spans for fields across all viewports', function () {
    $components = Form::components([
        'fields' => [
            [
                'type' => 'text',
                'name' => 'full_name',
                'label' => 'Full name',
                'column_span' => 7,
                '_responsive' => [
                    'mobile' => ['content' => ['column_span' => 12]],
                    'tablet_portrait' => ['content' => ['column_span' => 6]],
                    'tablet_landscape' => ['content' => ['column_span' => 4]],
                    'ultrawide' => ['content' => ['column_span' => 3]],
                ],
            ],
        ],
    ]);

    $field = $components[0] ?? null;

    expect($field)->toBeInstanceOf(TextInput::class);
    expect($field->getColumnSpan('default'))->toBe(12);
    expect($field->getColumnSpan('sm'))->toBe(6);
    expect($field->getColumnSpan('md'))->toBe(4);
    expect($field->getColumnSpan('lg'))->toBe(7);
    expect($field->getColumnSpan('xl'))->toBe(3);
});

it('uses default field span fallback when mobile responsive override is missing', function () {
    $components = Form::components([
        'fields' => [
            [
                'type' => 'text',
                'name' => 'nickname',
                'label' => 'Nickname',
                'column_span' => 5,
                '_responsive' => [
                    'tablet_landscape' => ['content' => ['column_span' => 3]],
                ],
            ],
        ],
    ]);

    $field = $components[0] ?? null;

    expect($field)->toBeInstanceOf(TextInput::class);
    expect($field->getColumnSpan('default'))->toBe(12);
    expect($field->getColumnSpan('sm'))->toBeNull();
    expect($field->getColumnSpan('md'))->toBe(3);
    expect($field->getColumnSpan('lg'))->toBe(5);
    expect($field->getColumnSpan('xl'))->toBeNull();
});

it('maps responsive wrapper columns for grid wrappers', function () {
    $components = Form::components([
        'fields' => [
            [
                'type' => 'wrapper',
                'wrapper_type' => 'grid',
                'columns' => 8,
                '_responsive' => [
                    'mobile' => ['content' => ['columns' => 2]],
                    'tablet_portrait' => ['content' => ['columns' => 3]],
                    'tablet_landscape' => ['content' => ['columns' => 4]],
                    'ultrawide' => ['content' => ['columns' => 10]],
                ],
                'children' => [
                    [
                        'type' => 'text',
                        'name' => 'email',
                        'label' => 'Email',
                    ],
                ],
            ],
        ],
    ]);

    $grid = $components[0] ?? null;

    expect($grid)->toBeInstanceOf(Grid::class);
    expect($grid->getColumns('default'))->toBe(2);
    expect($grid->getColumns('sm'))->toBe(3);
    expect($grid->getColumns('md'))->toBe(4);
    expect($grid->getColumns('lg'))->toBe(8);
    expect($grid->getColumns('xl'))->toBe(10);
});

it('uses default wrapper fallback when mobile columns are missing', function () {
    $components = Form::components([
        'fields' => [
            [
                'type' => 'wrapper',
                'wrapper_type' => 'group',
                'columns' => 4,
                '_responsive' => [
                    'tablet_portrait' => ['content' => ['columns' => 2]],
                ],
                'children' => [
                    [
                        'type' => 'text',
                        'name' => 'company',
                        'label' => 'Company',
                    ],
                ],
            ],
        ],
    ]);

    $group = $components[0] ?? null;

    expect($group)->toBeInstanceOf(Group::class);
    expect($group->getColumns('default'))->toBe(1);
    expect($group->getColumns('sm'))->toBe(2);
    expect($group->getColumns('md'))->toBeNull();
    expect($group->getColumns('lg'))->toBe(4);
});

it('propagates responsive overrides from legacy builder payloads to mapped fields', function () {
    $components = Form::components([
        'components' => [
            [
                'id' => 'legacy-field',
                'type' => 'text',
                'parent_id' => null,
                'order' => 0,
                'props' => [
                    'content' => [
                        'name' => 'legacy_name',
                        'label' => 'Legacy Name',
                        'column_span' => 6,
                    ],
                    '_responsive' => [
                        'mobile' => ['content' => ['column_span' => 12]],
                        'tablet_portrait' => ['content' => ['column_span' => 5]],
                        'ultrawide' => ['content' => ['column_span' => 4]],
                    ],
                ],
            ],
        ],
    ]);

    $field = $components[0] ?? null;

    expect($field)->toBeInstanceOf(TextInput::class);
    expect($field->getName())->toBe('legacy_name');
    expect($field->getColumnSpan('default'))->toBe(12);
    expect($field->getColumnSpan('sm'))->toBe(5);
    expect($field->getColumnSpan('md'))->toBeNull();
    expect($field->getColumnSpan('lg'))->toBe(6);
    expect($field->getColumnSpan('xl'))->toBe(4);
});
