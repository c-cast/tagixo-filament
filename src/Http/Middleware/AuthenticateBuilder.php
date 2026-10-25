<?php

namespace Tagixo\Filament\Http\Middleware;

use Filament\Facades\Filament;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Where a guest goes when the editor asks who they are.
 *
 * The core's builder routes are behind `auth`, and an unauthenticated request
 * ends at `route('login')` — the name Laravel's exception handler falls back
 * to. An application whose only front door is a Filament panel has no route by
 * that name: Filament calls its own `filament.<panel>.auth.login`. So a session
 * that expires while the editor is open answers with a RouteNotFoundException,
 * which the browser shows as a 500, instead of a login screen.
 *
 * This middleware sits on those routes only and changes nothing for an
 * application that has already said where guests go: a redirect callback of its
 * own wins, a `login` route of its own is left to the handler as before, and
 * only when there is neither does the panel's own login page answer.
 */
class AuthenticateBuilder extends Authenticate
{
    protected function redirectTo(Request $request): ?string
    {
        try {
            // Laravel's own default is `fn () => route('login')`, registered
            // before the application is asked anything — so it is not a choice
            // and it throws exactly in the case this class is here for. An
            // application that did choose answers with a usable URL, and keeps
            // it.
            $chosen = parent::redirectTo($request);
        } catch (RouteNotFoundException) {
            $chosen = null;
        }

        if ($chosen !== null) {
            return $chosen;
        }

        // A `login` route of its own: hand the decision back, as before.
        if (Route::has('login')) {
            return null;
        }

        return Filament::getCurrentOrDefaultPanel()?->getLoginUrl();
    }
}
