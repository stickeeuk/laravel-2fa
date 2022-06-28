<?php

namespace Stickee\Laravel2fa;

use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Stickee\Laravel2fa\Commands\Reauthenticate;
use Stickee\Laravel2fa\Contracts\QrCodeGenerator;
use Stickee\Laravel2fa\Contracts\RecoveryCodeGenerator as RecoveryCodeGeneratorInterface;
use Stickee\Laravel2fa\Contracts\StateStore;
use Stickee\Laravel2fa\Contracts\UserDataManager as UserDataManagerInterface;
use Stickee\Laravel2fa\Http\Middleware\Laravel2fa;
use Stickee\Laravel2fa\Services\BaconQrCodeGenerator;
use Stickee\Laravel2fa\Services\Laravel2faService;
use Stickee\Laravel2fa\Services\RecoveryCodeGenerator;
use Stickee\Laravel2fa\Services\UserDataManager;

/**
 * 2FA service provider
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-2fa.php', 'laravel-2fa'
        );

        $this->app->bind(StateStore::class, config('laravel-2fa.state_store'));
        $this->app->bind(ImageBackEndInterface::class, config('laravel-2fa.qr_code_generator'));
        $this->app->bind(QrCodeGenerator::class, BaconQrCodeGenerator::class);
        $this->app->bind(RecoveryCodeGeneratorInterface::class, RecoveryCodeGenerator::class);
        $this->app->bind(UserDataManagerInterface::class, UserDataManager::class);
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-2fa');
        $this->loadViewsFrom(__DIR__ . '/../../../laravel/nova/resources/views', 'nova');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'laravel-2fa');

        $this->app['router']->aliasMiddleware('laravel-2fa', Laravel2fa::class);

        foreach (config('laravel-2fa.middleware_groups') as $group => $middleware) {
            $this->app['router']->pushMiddlewareToGroup($group, $middleware);
        }

        $this->registerRoutes();

        foreach (config('laravel-2fa.drivers') as $driverName => $class) {
            $class::boot($driverName);
        }

        if (!$this->app->runningInConsole()) {
            return;
        }

        // Function not available and 'publish' not relevant in Lumen
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/laravel-2fa.php' => config_path('laravel-2fa.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views/laravel' => resource_path('views/vendor/laravel-2fa/laravel'),
            ], 'laravel-views');

            $this->publishes([
                __DIR__ . '/resources/lang' => resource_path('lang/vendor/laravel-2fa'),
            ], 'translations');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->commands([
            Reauthenticate::class,
        ]);
    }

    /**
     * Register routes
     */
    private function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(config('laravel-2fa.routes_middleware'))
            ->as('laravel-2fa.')
            ->group(__DIR__ . '/../routes/routes.php');
    }
}
