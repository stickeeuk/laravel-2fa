<?php

namespace Stickee\Laravel2fa\Http\Middleware;

use Closure;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stickee\Laravel2fa\Http\Controllers\Laravel2faController;
use Stickee\Laravel2fa\Models\Laravel2fa as Laravel2faModel;
use Stickee\Laravel2fa\Services\Laravel2faService;
use Stickee\Laravel2fa\Services\UserDataManager;

class Laravel2fa
{
    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request The request
     * @param \Closure $next The next method
     * @param null|string $guard  guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        $user = $request->user($guard);

        app()->when(Laravel2faService::class)
            ->needs(User::class)
            ->give(function () use ($guard) {
                return auth($guard)->user();
            });

        app()->when(UserDataManager::class)
            ->needs(User::class)
            ->give(function () use ($guard) {
                return auth($guard)->user();
            });

        // Don't do 2FA if it's disabled or the user is not logged in
        if (!config('laravel-2fa.enabled') || empty($user)) {
            return $next($request);
        }
        // Don't do 2FA for 2FA setup / validation routes
        if (Str::startsWith($request->route()->action['as'] ?? '', 'laravel-2fa')) {
            return $next($request);
        }

        // If the user doesn't have 2FA enabled...
        $laravel2fa = Laravel2faModel::getByModel($user);
        if (!$laravel2fa || (!$laravel2fa->enabled ?? false)) {
            // ... if it's required, force them to enable it
            if (config('laravel-2fa.required')) {
                $controller = app()->makeWith(Laravel2faController::class, ['guard' => $guard]);

                return response(app()->call([$controller, 'register']));
            } else {
                return $next($request);
            }
        }

        $service = app(Laravel2faService::class);

        if ($service->needsToAuthenticate() && !$service->isAuthenticated()) {
            if ($request->ajax()) {
                return response()->json(['error' => '2fa required'], 401);
            }

            $controller = app()->makeWith(Laravel2faController::class, ['guard' => $guard]);

            return app()->call([$controller, 'startAuthentication']);
        }

        return $next($request);
    }
}
