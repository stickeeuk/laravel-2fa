<?php

namespace Stickee\Laravel2fa\Drivers;

use Closure;
use Illuminate\Support\Facades\Route;
use Stickee\Laravel2fa\Contracts\Driver;
use Stickee\Laravel2fa\Contracts\UserDataManager;

abstract class AbstractDriver implements Driver
{
    /**
     * The driver name
     *
     * @var string $name
     */
    protected $name;

    /**
     * The user data manager
     *
     * @var \Stickee\Laravel2fa\Contracts\UserDataManager $userDataManager
     */
    private $userDataManager;

    /**
     * Constructor
     *
     * @param string $name The driver name
     * @param \Stickee\Laravel2fa\Contracts\UserDataManager $userDataManager The user data manager
     */
    public function __construct(string $name, UserDataManager $userDataManager)
    {
        $this->name = $name;
        $this->userDataManager = $userDataManager;
    }

    /**
     * Initialise user data before they register
     */
    public function initialiseUserData(): void
    {
        if (empty($this->getData())) {
            $this->reinitialiseUserData();
        }
    }

    /**
     * Get the users's data for this driver
     *
     * @return array
     */
    protected function getData(): array
    {
        return $this->userDataManager->getDriver($this->name);
    }

    /**
     * Set the users's data for this driver
     *
     * @param array $data The data
     */
    protected function setData(array $data): void
    {
        $this->userDataManager->setDriver($this->name, $data);
    }

    protected static function registerRoutes(string $name, Closure $function)
    {
        Route::middleware(config('laravel-2fa.routes_middleware'))
            ->as('laravel-2fa.' . $name . '.')
            ->group($function);
    }


}
