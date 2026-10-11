<?php

use Tagixo\Core\Services\BuilderModelRegistryService;
use Tagixo\Filament\Concerns\CleansBuilderStructure;
use Tagixo\Filament\Forms\Form;
use Tagixo\Filament\FormBuilder\Contracts\FilamentFieldModule;
use Tagixo\Filament\FormBuilder\Contracts\FilamentWrapperModule;
use Tagixo\Filament\FormBuilder\FilamentModuleRegistry;
use Tagixo\Filament\FormBuilder\Modules\TextInputField;
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

