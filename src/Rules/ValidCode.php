<?php

namespace Stickee\Laravel2fa\Rules;

use Illuminate\Contracts\Validation\Rule;
use Stickee\Laravel2fa\Exceptions\AuthenticationLockException;
use Stickee\Laravel2fa\Services\Laravel2faService;

class ValidCode implements Rule
{
    /**
     * The 2FA service
     *
     * @var \Stickee\Laravel2fa\Services\Laravel2faService $laravel2faService
     */
    private $laravel2faService;

    /**
     * The driver name
     *
     * @var null|string
     */
    private $driver;

    /**
     * The message to display to the user for an invalid code.
     *
     * @var string
     */
    private $message = 'The code was not valid';

    /**
     * Constructor
     *
     * @param \Stickee\Laravel2fa\Services\Laravel2faService $laravel2faService The 2FA service
     * @param null|string $driver The driver name, or null to use all
     */
    public function __construct(Laravel2faService $laravel2faService, ?string $driver = null)
    {
        $this->laravel2faService = $laravel2faService;
        $this->driver = $driver;
    }

   /**
     * Determine if the validation rule passes
     *
     * @param string $attribute The attribute name
     * @param mixed $value The value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            return $this->laravel2faService->verify($value, $this->driver);
        } catch (AuthenticationLockException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    /**
     * Get the validation error message
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
