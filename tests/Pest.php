<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| TagixoFilament Plugin Test Configuration
|--------------------------------------------------------------------------
|
| The suite runs in two contexts:
|
| - Standalone (`composer test` inside this package): uses the Testbench
|   TestCase from autoload-dev, which boots Filament + Tagixo providers.
| - From the host app's suite (root phpunit.xml includes this directory):
|   the package's autoload-dev is not registered there, so it falls back
|   to the host app's Tests\TestCase for full app context.
|
| Only plugin-internal tests live here. Tests that exercise host-app
| integration points (App\Models\*, App\Filament\Resources\*) stay in
| the root project's tests/ tree.
|
*/

uses(
    class_exists(Ccast\TagixoFilament\Tests\TestCase::class)
        ? Ccast\TagixoFilament\Tests\TestCase::class
        : TestCase::class
)->in(__DIR__);

uses()->beforeEach(function () {
    if (! app()->bound('translator')) {
        app()->singleton('translator', function () {
            return new class
            {
                public function get($key)
                {
                    return $key;
                }

                public function choice($key, $number)
                {
                    return $key;
                }

                public function getLocale()
                {
                    return 'en';
                }
            };
        });
    }
})->in(__DIR__);
