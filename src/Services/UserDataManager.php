<?php

namespace Stickee\Laravel2fa\Services;

use Illuminate\Foundation\Auth\User;
use Stickee\Laravel2fa\Contracts\UserDataManager as UserDataManagerInterface;
use Stickee\Laravel2fa\Models\Laravel2fa;

class UserDataManager implements UserDataManagerInterface
{
    /**
     * The user
     *
     * @var \Illuminate\Foundation\Auth\User $user The user
     */
    private $user;

    /**
     * The Laravel2fa instance
     *
     * @var \Stickee\Laravel2fa\Models\Laravel2fa $laravel2fa The Laravel2fa instance
     */
    private $laravel2fa;

    /**
     * Constructor
     *
     * @param \Illuminate\Foundation\Auth\User $user The user
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        $this->laravel2fa = Laravel2fa::where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey())
            ->first();
    }

    /**
     * Get the user's laravel 2fa instance
     *
     * @return null|Stickee\Laravel2fa\Models\Laravel2fa
     */
    public function get2faModel(): ?Laravel2fa
    {
        return $this->laravel2fa;
    }

    /**
     * Get the user's laravel 2fa instance or create a new one
     *
     * @return Stickee\Laravel2fa\Models\Laravel2fa
     */
    public function getOrCreate2faModel(): Laravel2fa
    {
        if (!$this->laravel2fa) {
            $this->laravel2fa = Laravel2fa::createByModel($this->user);
        }

        return $this->laravel2fa;
    }

    /**
     * Get the user's data
     *
     * @return array
     */
    public function get(): array
    {
        if (empty($this->laravel2fa->data)) {
            return [];
        }

        return $this->laravel2fa->data;
    }

    /**
     * Set the user's data
     *
     * @param array $data The user's data
     */
    public function set(array $data): void
    {
        if (!$this->laravel2fa) {
            $this->laravel2fa = Laravel2fa::createByModel($this->user);
        }

        $this->laravel2fa->update(['data' => $data]);
    }

    /**
     * Get a value in the user's data
     *
     * @param string $key The key
     * @param mixed $default The default value
     *
     * @return mixed
     */
    public function getValue($key, $default = null)
    {
        return $this->get()[$key] ?? $default;
    }

    /**
     * Set a value in the user's data
     *
     * @param string $key The key
     * @param string $value The value
     */
    public function setValue($key, $value): void
    {
        $data = $this->get();
        $data[$key] = $value;

        $this->set($data);
    }

    /**
     * Get the user's data for a driver
     *
     * @param string $driverName The driver name
     *
     * @return array
     */
    public function getDriver(string $driverName): array
    {
        $drivers = $this->getValue('drivers', []);

        return $drivers[$driverName] ?? [];
    }

    /**
     * Set the user's data for a driver
     *
     * @param string $driverName The driver name
     * @param array $data The user's driver data
     */
    public function setDriver(string $driverName, array $data): void
    {
        $drivers = $this->getValue('drivers', []);

        $drivers[$driverName] = $data;

        $this->setValue('drivers', $drivers);
    }
}
