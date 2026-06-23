<?php

use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoFilament\Concerns\CleansBuilderStructure;
use Ccast\TagixoFilament\Filament\Forms\Form;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentFieldModule;
use Ccast\TagixoFilament\FormBuilder\Contracts\FilamentWrapperModule;
use Ccast\TagixoFilament\FormBuilder\FilamentModuleRegistry;
use Ccast\TagixoFilament\FormBuilder\Modules\TextInputField;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

if (! class_exists('TagixoTestCustomFieldModule')) {
    final class TagixoTestCustomFieldModule implements FilamentFieldModule
    {
        public static function toFilamentField(
            array $field,
            BuilderModelRegistryService $modelRegistry,
            ?Closure $modelOptionsResolver = null,
        ): ?Component {
            return TextInput::make((string) ($field['name'] ?? 'custom_field'));
        }
    }
}

if (! class_exists('TagixoTestCustomWrapperModule')) {
    final class TagixoTestCustomWrapperModule implements FilamentWrapperModule
    {
        public static function toFilamentWrapper(
            array $field,
            array $children,
            BuilderModelRegistryService $modelRegistry,
            ?Closure $modelOptionsResolver = null,
        ): ?Component {
            return Group::make()->schema($children);
        }
    }
}

it('cleans builder structures recursively and preserves list ordering', function () {
    $cleaner = new class
    {
        use CleansBuilderStructure;

        public function clean(mixed $value): mixed
        {
            return $this->cleanStructure($value);
        }
    };

    $input = [
        'components' => [
            [
                'id' => 'a',
                'props' => [
                    'content' => [
                        'title' => 'Hello',
                        'subtitle' => '',
                        'null_value' => null,
                    ],
                    'spacing' => [],
                ],
            ],
            [
                'id' => 'b',
                'props' => [
                    'font_size_unit' => 'px',
                ],
            ],
        ],
        'body' => [
            'font_size_unit' => 'px',
            'empty' => '',
        ],
    ];

    expect($cleaner->clean($input))->toBe([
        'components' => [
            [
                'id' => 'a',
                'props' => [
                    'content' => [
                        'title' => 'Hello',
                    ],
                ],
            ],
            [
                'id' => 'b',
            ],
        ],
    ]);
});

it('registers custom module mappings through the registry and maps components', function () {
    $registry = (new FilamentModuleRegistry)->registerMany(
        fields: [
            'text' => TextInputField::class,
            'custom_text' => TagixoTestCustomFieldModule::class,
        ],
        wrappers: [
            'custom_group' => TagixoTestCustomWrapperModule::class,
        ],
    );

    expect($registry->getField('text'))->toBe(TextInputField::class);
    expect($registry->getField('custom_text'))->toBe(TagixoTestCustomFieldModule::class);
    expect($registry->getWrapper('custom_group'))->toBe(TagixoTestCustomWrapperModule::class);

    app()->instance(FilamentModuleRegistry::class, $registry);

    $components = Form::components([
        'fields' => [
            ['type' => 'custom_text', 'name' => 'external_custom'],
        ],
    ]);

    expect($components)->toHaveCount(1);
    expect($components[0])->toBeInstanceOf(TextInput::class);
    expect($components[0]->getName())->toBe('external_custom');
});

it('generates a builder page class via artisan command with custom options', function () {
    $class = 'SdkGeneratedBuilderPage'.Str::upper(Str::random(6));
    $path = app_path("Filament/Admin/Pages/{$class}.php");

    File::delete($path);

    $this->artisan('make:builder-page', [
        'name' => $class,
        '--context' => 'mail',
        '--title' => 'SDK Mail Builder',
        '--panel' => 'Admin',
    ])->assertExitCode(0);

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);
    expect($contents)->toContain("class {$class} extends BuilderPage");
    expect($contents)->toContain("protected static ?string \$title = 'SDK Mail Builder';");
    expect($contents)->toContain("return 'mail';");

    File::delete($path);
});

it('supports custom panel namespace when generating builder pages', function () {
    $class = 'SdkMarketingBuilder'.Str::upper(Str::random(6));
    $path = app_path("Filament/Marketing/Pages/{$class}.php");

    File::delete($path);

    $this->artisan('make:builder-page', [
        'name' => $class,
        '--panel' => 'Marketing',
    ])->assertExitCode(0);

    expect(File::exists($path))->toBeTrue();
    expect(File::get($path))->toContain('namespace App\\Filament\\Marketing\\Pages;');

    File::delete($path);
});
