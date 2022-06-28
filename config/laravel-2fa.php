<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Enabled
     |--------------------------------------------------------------------------
     |
     | Enable or disable 2-factor checking
     |
     */
    'enabled' => env('LARAVEL_2FA_ENABLED', true),

    /*
     |--------------------------------------------------------------------------
     | Required
     |--------------------------------------------------------------------------
     |
     | If 2FA is required or optional for users (force enrollment)
     |
     */
    'required' => env('LARAVEL_2FA_REQUIRED', false),

    'guards' => [
        'web' => [
            'routes_prefix' => '/2fa',
            'routes_middleware' => ['web', 'laravel-2fa:web'],
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Middleware groups
     |--------------------------------------------------------------------------
     |
     | Middleware groups to add 2FA to.
     | It will always only apply to authenticated users
     |
     */
    'middleware_groups' => ['web' => 'laravel-2fa:web', 'nova' => 'laravel-2fa:web'],

    /*
     |--------------------------------------------------------------------------
     | Routes middleware
     |--------------------------------------------------------------------------
     |
     | Middleware to apply to routes used by the 2FA system (e.g. /2fa/register)
     |
     */
    'routes_middleware' => ['web', 'auth:web'],

    /*
     |--------------------------------------------------------------------------
     | State store
     |--------------------------------------------------------------------------
     |
     | A class implementing \Stickee\Laravel2fa\Contracts\StateStore
     | to store state
     |
     */
    'state_store' => \Stickee\Laravel2fa\StateStores\SessionStateStore::class,

    /*
     |--------------------------------------------------------------------------
     | Session prefix
     |--------------------------------------------------------------------------
     |
     | Prefix for variables stored in the session to prevent collisions
     |
     */
    'session_prefix' => 'laravel-2fa',

    /*
     |--------------------------------------------------------------------------
     | Drivers
     |--------------------------------------------------------------------------
     |
     | A list of classes implementing \Stickee\Laravel2fa\Contracts\Driver.
     | The key should be the driver name
     |
     */
    'drivers' => [
        'google' => \Stickee\Laravel2fa\Drivers\Google::class,
        'twilio' => \Stickee\Laravel2fa\Drivers\Twilio::class,
    ],

    /*
     |--------------------------------------------------------------------------
     | QR code generator
     |--------------------------------------------------------------------------
     |
     | A backend for Bacon QR Code - one of:
     | \BaconQrCode\Renderer\Image\ImagickImageBackEnd
     | \BaconQrCode\Renderer\Image\SvgImageBackEnd
     | \BaconQrCode\Renderer\Image\EpsImageBackEnd
     |
     | To use a completeley custom generator see the documentation
     |
     */
    'qr_code_generator' => \BaconQrCode\Renderer\Image\SvgImageBackEnd::class,

    /*
     |--------------------------------------------------------------------------
     | QR code size
     |--------------------------------------------------------------------------
     |
     | The size in pixels of the QR code
     |
     */
    'qr_code_size' => 400,

    /*
     |--------------------------------------------------------------------------
     | Lifetime
     |--------------------------------------------------------------------------
     |
     | The number of minutes of inactivity a user is allowed before they must
     | reauthenticate. Set to zero for no limit
     |
     */
    'lifetime' => 0,

    /*
     |--------------------------------------------------------------------------
     | Keep-alive
     |--------------------------------------------------------------------------
     |
     | Whether to update the last activity time with each pageview. If set to
     | false then "lifetime" becomes time since login
     |
     */
    'keep_alive' => true,

    /*
     |--------------------------------------------------------------------------
     | Prevent reuse
     |--------------------------------------------------------------------------
     |
     | Prevent the user from logging in twice with the same code
     |
     */
    'prevent_reuse' => false,

    /*
     |--------------------------------------------------------------------------
     | Recovery codes count
     |--------------------------------------------------------------------------
     |
     | How many recovery codes to generate when a user registers
     |
     */
    'recovery_codes_count' => 10,

    /*
     |--------------------------------------------------------------------------
     | Redirect after login
     |--------------------------------------------------------------------------
     |
     | The URL to redirect to after logging in (if we don't know where the user
     | was trying to get to)
     |
     */
    'redirect_after_login' => '/',

    /*
     |--------------------------------------------------------------------------
     | Redirect after logout
     |--------------------------------------------------------------------------
     |
     | The URL to redirect to after logging out
     |
     */
    'redirect_after_logout' => '/',

    /*
     |--------------------------------------------------------------------------
     | The app name
     |--------------------------------------------------------------------------
     |
     | The app name to use in the authenticator (use null to use app.name)
     |
     */
    'app_name' => null,

    /*
     |--------------------------------------------------------------------------
     | Username attribute
     |--------------------------------------------------------------------------
     |
     | The attribute on the User model to use as the username
     | in the authenticator
     |
     */
    'username_attribute' => 'email',

    /*
     |--------------------------------------------------------------------------
     | Google 2FA specific values
     |--------------------------------------------------------------------------
     |
     | Any specific values relating to Google 2FA
     |
     */
    'google' => [
        'guards' => ['web'],
    ],

    /*
     |--------------------------------------------------------------------------
     | Twilio 2FA specific values
     |--------------------------------------------------------------------------
     |
     | Any specific values relating to Twilio 2FA
     |
     */
    'twilio' => [
        'guards' => ['web'],
        'from' => env('LARAVEL_2FA_TWILIO_FROM'),
        'sid' => env('LARAVEL_2FA_TWILIO_SID'),
        'token' => env('LARAVEL_2FA_TWILIO_TOKEN'),
        'message' => 'Here\'s your authentication code: [code]',
        'cooldown_in_minutes' => 10,
    ],

];
