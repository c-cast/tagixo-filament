<?php

use Ccast\TagixoFilament\FormBuilder\Reactivity\Contracts\ReactivityFunctionProvider;
use Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityActionRunner;
use Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityExpressionEvaluator;
use Ccast\TagixoFilament\FormBuilder\Reactivity\ReactivityFunctionRegistry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

uses(RefreshDatabase::class);

if (! class_exists('TagixoTestProviderStatic')) {
    final class TagixoTestProviderStatic implements ReactivityFunctionProvider
    {
        public function getFunctions(): array
        {
            return [
                new ExpressionFunction(
                    'tenant_code',
                    static fn (): string => 'null',
                    static fn (array $values): string => 'TENANT-42',
                ),
            ];
        }
    }
}

beforeEach(function () {
    app(ReactivityFunctionRegistry::class)->flush();
});

afterEach(function () {
    app(ReactivityFunctionRegistry::class)->flush();
});

it('registers direct and provider functions and describes them', function () {
    $registry = app(ReactivityFunctionRegistry::class);

    $registry
        ->register('double', fn (int $value): int => $value * 2)
        ->registerMany([
            'prefix' => fn (string $value): string => 'prefix-'.$value,
        ])
        ->addProvider(TagixoTestProviderStatic::class)
        ->addProvider(fn () => new class implements ReactivityFunctionProvider
        {
            public function getFunctions(): array
            {
                return [
                    new ExpressionFunction(
                        'tenant_name',
                        static fn (): string => 'null',
                        static fn (array $values): string => 'Acme SPA',
                    ),
                ];
            }
        });

    expect($registry->describe())->toContain('double', 'prefix', 'tenant_code', 'tenant_name');
});

it('evaluates built-in expressions and supports custom overrides', function () {
    $registry = app(ReactivityFunctionRegistry::class);
    $registry->register('slug', fn (string $value): string => 'custom-'.$value);

    $get = Mockery::mock(Get::class);
    $get->shouldReceive('__invoke')->once()->with('other')->andReturn('SECOND');

    $result = ReactivityExpressionEvaluator::evaluate(
        expression: "upper(trim(state)) ~ ':' ~ lower(get('other')) ~ ':' ~ slug('hello world')",
        state: '  first  ',
        get: $get,
    );

    expect($result)->toBe('FIRST:second:custom-hello world');
});

it('returns null on invalid expression syntax without throwing', function () {
    $get = Mockery::mock(Get::class);

    $result = ReactivityExpressionEvaluator::evaluate(
        expression: 'if(',
        state: 'anything',
        get: $get,
    );

    expect($result)->toBeNull();
});

it('runs reactive actions with transforms, conditions and expressions', function () {
    $get = Mockery::mock(Get::class);
    $get->shouldReceive('__invoke')->once()->with('enabled')->andReturn(true);
    $get->shouldReceive('__invoke')->once()->with('first_name')->andReturn('mario');
    $get->shouldReceive('__invoke')->once()->with('last_name')->andReturn('rossi');

    $set = Mockery::mock(Set::class);
    $set->shouldReceive('__invoke')->once()->with('data.slug', 'hello-world');
    $set->shouldReceive('__invoke')->once()->with('data.upper_copy', 'INITIAL');
    $set->shouldReceive('__invoke')->once()->with('data.legacy', null);
    $set->shouldReceive('__invoke')->once()->with('data.full_name', 'MARIO ROSSI');

    ReactivityActionRunner::run(
        actions: [
            [
                'action_type' => 'set_value',
                'target_field' => 'data.slug',
                'static_value' => 'Hello World',
                'transform' => 'slug',
            ],
            [
                'action_type' => 'copy_state',
                'target_field' => 'data.upper_copy',
                'transform' => 'upper',
            ],
            [
                'action_type' => 'clear_field',
                'target_field' => 'data.legacy',
            ],
            [
                'action_type' => 'set_expression',
                'target_field' => 'data.full_name',
                'expression' => "upper(get('first_name')) ~ ' ' ~ upper(get('last_name'))",
                'condition_field' => 'enabled',
                'condition_operator' => '==',
                'condition_value' => true,
            ],
        ],
        state: 'initial',
        set: $set,
        get: $get,
    );
});

it('skips actions when condition does not match', function () {
    $get = Mockery::mock(Get::class);
    $get->shouldReceive('__invoke')->once()->with('status')->andReturn('archived');

    $set = Mockery::mock(Set::class);
    $set->shouldNotReceive('__invoke');

    ReactivityActionRunner::run(
        actions: [
            [
                'action_type' => 'set_value',
                'target_field' => 'data.should_not_change',
                'static_value' => 'value',
                'condition_field' => 'status',
                'condition_operator' => '!=',
                'condition_value' => 'archived',
            ],
        ],
        state: 'state',
        set: $set,
        get: $get,
    );
});
