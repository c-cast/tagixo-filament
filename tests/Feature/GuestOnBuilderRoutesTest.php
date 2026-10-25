<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tagixo\Filament\Http\Middleware\AuthenticateBuilder;

/*
 * The core guards its builder routes with `auth`, and an unauthenticated
 * request ends at `route('login')` — a route an application whose only front
 * door is a Filament panel does not have. A session that expired while the
 * editor was open answered with a RouteNotFoundException, which the browser
 * shows as a 500.
 */

// The callback is a static on Laravel's middleware and outlives a test, so
// every test starts from "the application has said nothing".
beforeEach(fn () => AuthenticateBuilder::redirectUsing(fn () => null));

function redirectFor(): ?string
{
    $middleware = new AuthenticateBuilder(app('auth'));

    return (fn (): ?string => $this->redirectTo(Request::create('/tagixo/builder/embed')))
        ->call($middleware);
}

it('puts the panel login on the builder routes of the core', function () {
    expect(config('tagixo.route_middleware'))->toBe(['web', AuthenticateBuilder::class]);
});

it('sends a guest to the panel when the application has no login route', function () {
    expect(Route::has('login'))->toBeFalse()
        ->and(redirectFor())->toBe(Filament\Facades\Filament::getDefaultPanel()->getLoginUrl());
});

it('leaves an application that has its own login route alone', function () {
    Route::get('/login', fn () => 'login')->name('login');
    // Registered after the application booted: the name index is stale until asked.
    Route::getRoutes()->refreshNameLookups();

    // Null hands the decision back to Laravel, which goes to route('login') —
    // exactly what an application with its own login expects.
    expect(redirectFor())->toBeNull();
});

it('is not fooled by the default Laravel registers before asking', function () {
    // `withMiddleware()` always starts with `fn () => route('login')`, so the
    // callback is set even in an application that never chose anything — and
    // in a panel-only one it throws, which is the whole case for this class.
    AuthenticateBuilder::redirectUsing(fn (): string => route('login'));

    expect(redirectFor())->toBe(Filament\Facades\Filament::getDefaultPanel()->getLoginUrl());
});

it('leaves a redirect the application chose itself alone', function () {
    AuthenticateBuilder::redirectUsing(fn (): string => '/somewhere-of-mine');

    expect(redirectFor())->toBe('/somewhere-of-mine');
});
