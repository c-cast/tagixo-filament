<?php

use Illuminate\Support\Facades\Route;
use Tagixo\Filament\Http\Controllers\TemplateBodyController;

/*
|--------------------------------------------------------------------------
| Routes of the SDK
|--------------------------------------------------------------------------
|
| One route, and only because a redirect has to happen server-side: the body of
| a model template is a page that may not exist yet, so it is created on the way
| to the editor.
|
*/

Route::middleware(
    config('tagixo.route_middleware', ['web', 'auth'])
)->group(function () {
    Route::get('/tagixo/filament/templates/{layout}/body', TemplateBodyController::class)
        ->name('tagixo-filament.templates.body');
});
