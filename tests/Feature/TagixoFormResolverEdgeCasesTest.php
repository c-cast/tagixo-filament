<?php

use Ccast\TagixoFilament\Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

uses(RefreshDatabase::class);

if (! class_exists('TagixoDummyLivewireFormConsumer')) {
    class TagixoDummyLivewireFormConsumer extends Component
    {
        public function render()
        {
            return '';
        }
    }
}

it('keeps explicit json definitions untouched when passed as strings', function () {
    $json = json_encode([
        'fields' => [
            ['type' => 'text', 'name' => 'json_input', 'label' => 'Json Input'],
        ],
    ], JSON_THROW_ON_ERROR);

    $components = Form::components($json);

    expect($components)->toHaveCount(1);
    expect($components[0])->toBeInstanceOf(TextInput::class);
    expect($components[0]->getName())->toBe('json_input');
});

/**
 * The plugin's canonical tgx_forms schema stores the form payload in a
 * `schema` JSON column; legacy consumer migrations use `fields` instead.
 * The resolver supports both, so the tests insert into whichever column
 * the current context provides.
 */
function insertTgxForm(int $id, string $title, string $slug, array $payload): void
{
    $column = Schema::hasColumn('tgx_forms', 'schema') ? 'schema' : 'fields';

    DB::table('tgx_forms')->insert([
        'id' => $id,
        'title' => $title,
        'slug' => $slug,
        $column => json_encode($payload, JSON_THROW_ON_ERROR),
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('infers livewire target type when resolving automatic bindings', function () {
    insertTgxForm(5001, 'Livewire Binding Form', 'livewire-binding-form', [
        'fields' => [
            ['type' => 'text', 'name' => 'livewire_field', 'label' => 'Livewire Field'],
        ],
    ]);

    DB::table('tgx_form_bindings')->insert([
        'form_schema_id' => 5001,
        'target_type' => 'livewire',
        'target_class' => TagixoDummyLivewireFormConsumer::class,
        'target_operation' => null,
        'priority' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $components = Form::components(
        definition: null,
        targetClass: TagixoDummyLivewireFormConsumer::class,
        targetType: null,
    );

    expect($components)->toHaveCount(1);
    expect($components[0])->toBeInstanceOf(TextInput::class);
    expect($components[0]->getName())->toBe('livewire_field');
});

it('falls back to legacy bindings table when tgx_form_bindings is missing', function () {
    Schema::dropIfExists('tgx_form_bindings');

    Schema::create('tagixo_filament_form_bindings', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('form_schema_id');
        $table->string('target_type', 50)->default('resource');
        $table->string('target_class');
        $table->string('target_operation', 50)->nullable();
        $table->unsignedInteger('priority')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    insertTgxForm(6001, 'Legacy Binding Form', 'legacy-binding-form', [
        'fields' => [
            ['type' => 'text', 'name' => 'legacy_field', 'label' => 'Legacy Field'],
        ],
    ]);

    DB::table('tagixo_filament_form_bindings')->insert([
        'form_schema_id' => 6001,
        'target_type' => 'class',
        'target_class' => 'Acme\\Legacy\\Target',
        'target_operation' => null,
        'priority' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $components = Form::components(
        definition: null,
        targetClass: 'Acme\\Legacy\\Target',
        targetType: 'class',
    );

    expect($components)->toHaveCount(1);
    expect($components[0]->getName())->toBe('legacy_field');
});

it('falls back to form_schemas table when tgx_forms table is missing', function () {
    Schema::dropIfExists('tgx_forms');

    Schema::create('form_schemas', function ($table): void {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();
        $table->json('fields')->nullable();
        $table->text('description')->nullable();
        $table->string('status')->default('draft');
        $table->timestamps();
    });

    DB::table('form_schemas')->insert([
        'id' => 7001,
        'title' => 'Legacy Forms Table',
        'slug' => 'legacy-forms-table',
        'fields' => json_encode([
            ['type' => 'text', 'name' => 'legacy_table_field', 'label' => 'Legacy Table Field'],
        ], JSON_THROW_ON_ERROR),
        'description' => null,
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $components = Form::components(7001);

    expect($components)->toHaveCount(1);
    expect($components[0])->toBeInstanceOf(TextInput::class);
    expect($components[0]->getName())->toBe('legacy_table_field');
});
