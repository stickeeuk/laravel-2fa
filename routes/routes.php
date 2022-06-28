<?php

use Illuminate\Support\Facades\Route;
use Stickee\Laravel2fa\Http\Controllers\Laravel2faController;

foreach (config('laravel-2fa.guards') as $guard => $data) {
    Route::middleware($data['routes_middleware'])->prefix($data['routes_prefix'])->name("$guard.")->group(function () use ($guard) {
        $closure = function ($method) use ($guard) {
            return function () use ($guard, $method) {
                $controller = app()->makeWith(Laravel2faController::class, ['guard' => $guard]);
                return app()->call([$controller, $method]);
            };
        };

        Route::post('confirm', $closure('confirm'))->name('confirm');
        Route::get('start-authentication', $closure('startAuthentication'))->name('start-authentication');
        Route::get('authenticate', $closure('authenticate'))->name('authenticate');
        Route::post('do-authentication', $closure('doAuthentication'))->name('do-authentication');
        Route::post('logout', $closure('logout'))->name('logout');
    });
}
