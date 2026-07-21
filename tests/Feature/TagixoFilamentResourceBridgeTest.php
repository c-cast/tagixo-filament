<?php

/**
 * Smoke tests for the Filament resource bridges that ship with
 * `ccast/tagixo-filament` and expose the plugin's FormSchema / Slider
 * models as panel resources. These tests assert the SHAPE of the bridge
 * — model wiring, page registry, navigation, route names used by the
 * external "Open Builder" / "Create new" actions — without booting a
 * full Filament panel.
 *
 * If a future refactor changes the plugin route names
 * (`tagixo.forms.*` / `tagixo.sliders.*`) or accidentally adds a
 * panel-internal Build/Create page, these tests fail loudly.
 */

use Ccast\Tagixo\Models\FormSchema;
use Ccast\Tagixo\Models\Slider;
use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Ccast\TagixoFilament\Filament\Resources\Forms\Pages\EditForm;
use Ccast\TagixoFilament\Filament\Resources\Forms\Pages\ListForms;
use Ccast\TagixoFilament\Filament\Resources\Forms\Schemas\FormForm;
use Ccast\TagixoFilament\Filament\Resources\Forms\Tables\FormsTable;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Pages\EditSlider;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Pages\ListSliders;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Schemas\SliderForm;
use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;
use Ccast\TagixoFilament\Filament\Resources\Sliders\Tables\SlidersTable;
use Ccast\TagixoFilament\TagixoFilamentPlugin;
use Filament\Panel;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Route;

describe('FormResource (Filament bridge)', function () {
    it('extends Filament Resource', function () {
        expect(is_subclass_of(FormResource::class, Resource::class))->toBeTrue();
    });

    it('targets the plugin FormSchema model', function () {
        $rp = new ReflectionClass(FormResource::class);
        expect($rp->getStaticPropertyValue('model'))->toBe(FormSchema::class);
    });

    it('registers only metadata pages (no in-panel create or build)', function () {
        // index + edit are the bridge pages; preview-app is a metadata/preview
        // page (not authoring). The contract is: NO in-panel create or build —
        // those happen via the plugin's external builder routes.
        $pages = FormResource::getPages();
        expect($pages)->toHaveKey('index')
            ->and($pages)->toHaveKey('edit')
            ->and($pages)->not->toHaveKey('create')
            ->and($pages)->not->toHaveKey('build');
    });

    it('ListForms and EditForm are properly typed', function () {
        expect(is_subclass_of(ListForms::class, ListRecords::class))->toBeTrue();
        expect(is_subclass_of(EditForm::class, EditRecord::class))->toBeTrue();
    });

    it('uses the Visual Builder navigation group', function () {
        expect(FormResource::getNavigationGroup())->toBe('Visual Builder');
    });

    it('exposes the Schema and Table assembler classes', function () {
        expect(method_exists(FormForm::class, 'configure'))->toBeTrue();
        expect(method_exists(FormsTable::class, 'configure'))->toBeTrue();
    });
});

describe('SliderResource (Filament bridge)', function () {
    it('extends Filament Resource', function () {
        expect(is_subclass_of(SliderResource::class, Resource::class))->toBeTrue();
    });

    it('targets the plugin Slider model', function () {
        $rp = new ReflectionClass(SliderResource::class);
        expect($rp->getStaticPropertyValue('model'))->toBe(Slider::class);
    });

    it('registers only metadata pages (no in-panel create or build)', function () {
        $pages = SliderResource::getPages();
        expect(array_keys($pages))->toEqualCanonicalizing(['index', 'edit'])
            ->and($pages)->not->toHaveKey('create')
            ->and($pages)->not->toHaveKey('build');
    });

    it('ListSliders and EditSlider are properly typed', function () {
        expect(is_subclass_of(ListSliders::class, ListRecords::class))->toBeTrue();
        expect(is_subclass_of(EditSlider::class, EditRecord::class))->toBeTrue();
    });

    it('uses the Visual Builder navigation group', function () {
        expect(SliderResource::getNavigationGroup())->toBe('Visual Builder');
    });

    it('exposes the Schema and Table assembler classes', function () {
        expect(method_exists(SliderForm::class, 'configure'))->toBeTrue();
        expect(method_exists(SlidersTable::class, 'configure'))->toBeTrue();
    });
});

describe('Plugin route names contract', function () {
    /*
     * The bridge expects four plugin routes to exist with these exact
     * names because tables and pages reference them via `route(...)`.
     * If the plugin renames any of them, the panel actions silently 500
     * — this guards against drift.
     */
    it('registers the route', function (string $name) {
        expect(Route::has($name))
            ->toBeTrue("Missing plugin route: {$name}");
    })->with([
        'tagixo.forms.new' => 'tagixo.forms.new',
        'tagixo.forms.edit' => 'tagixo.forms.edit',
        'tagixo.sliders.new' => 'tagixo.sliders.new',
        'tagixo.sliders.edit' => 'tagixo.sliders.edit',
    ]);
});

describe('TagixoFilamentPlugin registration', function () {
    /*
     * Captures the resource classes the plugin passes to
     * `$panel->resources([...])` by mocking the Filament Panel.
     */
    function capturedResources(): array
    {
        $captured = [];

        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('resources')
            ->andReturnUsing(function (array $list) use (&$captured, $panel) {
                $captured = array_merge($captured, $list);

                return $panel;
            });
        // The plugin also registers panel pages (e.g. Site Scripts); accept and
        // ignore that call so the mock doesn't reject it.
        $panel->shouldReceive('pages')->andReturn($panel);

        TagixoFilamentPlugin::make()->register($panel);

        return $captured;
    }

    it('registers FormResource', function () {
        expect(capturedResources())->toContain(FormResource::class);
    });

    it('registers SliderResource', function () {
        expect(capturedResources())->toContain(SliderResource::class);
    });

    it('registers MenuResource', function () {
        expect(capturedResources())->toContain(MenuResource::class);
    });
});
