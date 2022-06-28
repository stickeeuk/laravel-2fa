<?php

namespace Stickee\Laravel2fa\Exceptions;

use Exception;

/**
 * An exception to throw if the authentication is on cooldown since the last attempt.
 * E.g. an authentication code via SMS is only available to be sent every 5 minutes.
 */
class AuthenticationOnCooldownException extends AuthenticationFailedToStartException
{
    public int $availableAt;

    /**
     * Constructor
     *
     * @param int $availableAt The timestamp when the next authentication is available.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param null|Exception $previous The previous exception.
     */
    public function __construct(int $availableAt, string $message, $code = 0, Exception $previous = null)
    {
        $this->availableAt = $availableAt;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'available_at' => $this->availableAt,
            ]
        );
    }
}
