<?php

namespace Stickee\Laravel2fa\Services;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Stickee\Laravel2fa\Contracts\Driver;
use Stickee\Laravel2fa\Contracts\RecoveryCodeGenerator;
use Stickee\Laravel2fa\Contracts\StateStore;
use Stickee\Laravel2fa\Exceptions\AuthenticationFailedToStartException;

class Laravel2faService
{
    /**
     * The current user
     *
     * @var \Illuminate\Foundation\Auth\User $user
     */
    private $user;

    /**
     * The state store
     *
     * @var \Stickee\Laravel2fa\Contracts\StateStore $stateStore
     */
    private $stateStore;

    /**
     * The keep-alive service
     *
     * @var \Stickee\Laravel2fa\Services\KeepAlive $keepAlive
     */
    private $keepAlive;

    /**
     * The recovery code generator
     *
     * @var \Stickee\Laravel2fa\Contracts\RecoveryCodeGenerator $recoveryCodeGenerator
     */
    private $recoveryCodeGenerator;

    /**
     * The user data manager
     *
     * @var \Stickee\Laravel2fa\Contracts\UserDataManager $userDataManager
     */
    private $userDataManager;

    /**
     * The enabled driver names
     *
     * @var string[] $drivers
     */
    private $drivers;

    /**
     * Constructor
     *
     * @param \Illuminate\Foundation\Auth\User $user The current user
     * @param \Stickee\Laravel2fa\Contracts\StateStore $stateStore The state store
     * @param \Stickee\Laravel2fa\Services\KeepAlive $keepAlive The keep-alive service
     * @param \Stickee\Laravel2fa\Contracts\RecoveryCodeGenerator $recoveryCodeGenerator The recovery code generator
     * @param \Stickee\Laravel2fa\Contracts\UserDataManager $userDataManager The user data manager
     */
    public function __construct(
        User $user,
        StateStore $stateStore,
        KeepAlive $keepAlive,
        RecoveryCodeGenerator $recoveryCodeGenerator,
        UserDataManager $userDataManager
    ) {
        $this->user = $user;
        $this->stateStore = $stateStore;
        $this->keepAlive = $keepAlive;
        $this->recoveryCodeGenerator = $recoveryCodeGenerator;
        $this->userDataManager = $userDataManager;
        $this->drivers = config('laravel-2fa.drivers');
    }

    /**
     * Whether the user needs to authenticate
     *
     * @return bool
     */
    public function needsToAuthenticate(): bool
    {
        return $this->userDataManager->get2faModel()->enabled ?? false;
    }

    /**
     * Whether the user is authenticated
     */
    public function isAuthenticated(): bool
    {
        if (!$this->stateStore->get($this->getStateName('authenticated'), false)) {
            return false;
        }

        if ($this->keepAlive->expired()) {
            return false;
        }

        return true;
    }

    /**
     * Verify a code is correct
     *
     * @param string $code The code
     * @param null|string $driverName The driver name, or null to check all drivers + recovery codes
     *
     * @return bool
     */
    public function verify(string $code, ?string $driverName = null): bool
    {
        $lastAuthenticated = $this->getLastAuthenticated();
        $newerThan = config('laravel-2fa.prevent_reuse') && isset($lastAuthenticated)
            ? $lastAuthenticated
            : null;

        if ($driverName !== null) {
            return $this->make($driverName)->verify($code, $newerThan);
        }

        foreach (array_keys($this->drivers) as $driverName) {
            if ($this->isEnabled($driverName)) {
                if ($this->make($driverName)->verify($code, $newerThan)) {
                    return true;
                }
            }
        }

        $recoveryCodes = $this->getRecoveryCodes();

        foreach ($recoveryCodes as $i => $recoveryCode) {
            if (Hash::check($code, $recoveryCode)) {
                unset($recoveryCodes[$i]);

                $this->setRecoveryCodes(array_values($recoveryCodes));

                return true;
            }
        }

        return false;
    }

    /**
     * Mark the user as being authenticated or not
     *
     * @param bool $authenticated If the user is authenticated
     */
    public function setAuthenticated(bool $authenticated = true): void
    {
        $this->stateStore->put($this->getStateName('authenticated'), $authenticated);

        $this->keepAlive->updateLastActivityTime();

        if ($authenticated) {
            $this->updateLastAuthenticated();
        }
    }

    /**
     * Initialise the user's data
     *
     * @return array
     */
    public function initialiseUserData(): array
    {
        foreach ($this->drivers as $driverName => $class) {
            $this->make($driverName)->initialiseUserData();
        }

        if (empty($this->getRecoveryCodes())) {
            return $this->updateRecoveryCodes();
        }

        return [];
    }

    /**
     * Get data for the registration view
     *
     * @return array
     */
    public function getRegistrationViewData(): array
    {
        $data = [];

        foreach ($this->drivers as $driverName => $class) {
            $data[$driverName] = $this->make($driverName)->getRegistrationViewData();
        }

        return $data;
    }

    /**
     * Get the prefixed state name
     *
     * @param string $name The unprefixed name
     *
     * @return string
     */
    private function getStateName($name): string
    {
        return config('state_prefix', 'laravel-2fa')
            . '.' . $this->user->id
            . '.' . $name;
    }

    /**
     * Start the authentication process (e.g. send SMS)
     * Returns a list of enabled drivers
     *
     * @return array
     */
    public function startAuthentication(): array
    {
        $enabledDrivers = [];

        foreach ($this->drivers as $driverName => $class) {
            if ($this->isEnabled($driverName)) {
                $enabledDrivers[$driverName] = ['started' => true];

                try {
                    $this->make($driverName)->startAuthentication();
                } catch (AuthenticationFailedToStartException $e) {
                    $enabledDrivers[$driverName]['started'] = false;
                    $enabledDrivers[$driverName]['exception'] = $e->toArray();
                }
            }
        }

        return $enabledDrivers;
    }

    /**
     * Enable a driver for the user
     *
     * @param string $driverName The driver name
     */
    public function enable(string $driverName): void
    {
        $laravel2fa = $this->userDataManager->getOrCreate2faModel();

        if (!$laravel2fa->enabled) {
            $laravel2fa->update(['enabled' => true]);
        }

        $enabled = $this->userDataManager->getValue('enabled', []);
        $enabled[$driverName] = true;

        $this->userDataManager->setValue('enabled', $enabled);
    }

    /**
     * Disable a driver for the user
     *
     * @param string $driverName The driver name
     */
    public function disable(string $driverName): void
    {
        $enabled = $this->userDataManager->getValue('enabled', []);
        $enabled[$driverName] = false;

        $this->userDataManager->setValue('enabled', $enabled);

        if (empty(array_filter($enabled))) {
            $this->user->laravel2fa_enabled = false;
            $this->user->save();
        }
    }

    /**
     * Whether a driver is enabled
     *
     * @param string $driverName The driver name
     *
     * @return bool
     */
    public function isEnabled(string $driverName): bool
    {
        $enabled = $this->userDataManager->getValue('enabled', []);

        return !empty($enabled[$driverName]);
    }

    /**
     * Construct a driver
     *
     * @param string $driverName The driver name
     *
     * @return \Stickee\Laravel2fa\Contracts\Driver
     */
    public function make(string $driverName): Driver
    {
        return app()->makeWith(
            $this->drivers[$driverName],
            [
                'name' => $driverName,
                'user' => $this->user,
                'userDataManager' => $this->userDataManager,
            ]
        );
    }

    /**
     * Get the last authenticated timestamp
     *
     * @return null|int
     */
    private function getLastAuthenticated(): ?int
    {
        return $this->userDataManager->getValue('last_authenticated');
    }

    /**
     * Update the last authenticated timestamp
     */
    private function updateLastAuthenticated(): void
    {
        $this->userDataManager->setValue('last_authenticated', time());
    }

    /**
     * Get the user's hashed recovery codes
     *
     * @return array
     */
    public function getRecoveryCodes(): array
    {
        return $this->userDataManager->getValue('recovery_codes', []);
    }

    /**
     * Save recovery codes
     *
     * @param array $recoveryCodes The recovery codes
     */
    private function setRecoveryCodes(array $recoveryCodes): void
    {
        $this->userDataManager->setValue('recovery_codes', $recoveryCodes);
    }

    /**
     * Generate, save and return new recovery codes
     *
     * @return array
     */
    public function updateRecoveryCodes(): array
    {
        $codes = $this->recoveryCodeGenerator->get(config('laravel-2fa.recovery_codes_count'));
        $hashedCodes = [];

        foreach ($codes as $i => $code) {
            $hashedCodes[$i] = Hash::make($code);
        }

        $this->setRecoveryCodes($hashedCodes);

        return $codes;
    }
}
