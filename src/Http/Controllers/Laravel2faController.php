<?php

namespace Stickee\Laravel2fa\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Stickee\Laravel2fa\Http\Requests\AuthenticateRequest;
use Stickee\Laravel2fa\Services\Laravel2faService;

class Laravel2faController extends Controller
{
    /**
     * The guard to use on the routes.
     *
     * @var string
     */
    private $guard;

    /**
     * Construtor
     */
    public function __construct(string $guard)
    {
        $this->guard = $guard;

        $this->middleware(function ($request, $next) {
            $service = app(Laravel2faService::class);

            if ($service->isAuthenticated()) {
                abort(401);
            }

            return $next($request);
        })->only('startAuthentication', 'authenticate', 'doAuthentication');
    }

    /**
     * Show the 2fa registration page
     *
     * @param \Illuminate\Http\Request $request The request
     * @param \Stickee\Laravel2fa\Services\Laravel2faService $service The 2FA service
     *
     * @return Illuminate\View\View
     */
    public function register(Request $request, Laravel2faService $service)
    {
        $recoveryCodes = $service->initialiseUserData();

        return view(
            'laravel-2fa::laravel.register',
            [
                'drivers' => array_keys(config('laravel-2fa.drivers')),
                'recoveryCodes' => $recoveryCodes,
            ]
        );
    }

    /**
     * Ask the user to authenticate
     *
     * @param \Illuminate\Http\Request $request The request
     * @param \Stickee\Laravel2fa\Services\Laravel2faService $service The 2FA service
     *
     * @return Illuminate\View\View
     */
    public function startAuthentication(Request $request, Laravel2faService $service)
    {
        $data = [
            'enabledDrivers' => $service->startAuthentication(),
        ];

        $request->session()->flash('laravel-2fa.start-authentication', $data);
        $request->session()->put('laravel-2fa.redirect_url', $request->url());

        return redirect(route("laravel-2fa.{$this->guard}.authenticate"));
    }

    /**
     * Ask the user to authenticate
     *
     * @param \Illuminate\Http\Request $request The request
     *
     * @return Illuminate\View\View
     */
    public function authenticate(Request $request)
    {
        $data = $request->session()->pull('laravel-2fa.start-authentication', []);
        $data['guard'] = $this->guard;

        return view('laravel-2fa::laravel.authenticate', $data);
    }

    /**
     * Authenticate the user
     *
     * @param \Stickee\Laravel2fa\Http\Requests\AuthenticateRequest $request The request
     * @param \Stickee\Laravel2fa\Services\Laravel2faService $service The 2FA service
     *
     * @return \Illuminate\Http\Redirect
     */
    public function doAuthentication(AuthenticateRequest $request, Laravel2faService $service)
    {
        // AuthenticateRequest has already done the verification
        $service->setAuthenticated(true);

        $redirect = config('laravel-2fa.redirect_after_login', '/');
        $redirect = $request->session()->pull('laravel-2fa.redirect_url', $redirect);

        return redirect($redirect);
    }

    /**
     * Log the user out
     *
     * @param \Stickee\Laravel2fa\Services\Laravel2faService $service The 2FA service
     *
     * @return \Illuminate\Http\Redirect
     */
    public function logout(Laravel2faService $service)
    {
        $service->setAuthenticated(false);
        Auth::logout();

        return redirect(config('laravel-2fa.redirect_after_logout', '/'));
    }
}
