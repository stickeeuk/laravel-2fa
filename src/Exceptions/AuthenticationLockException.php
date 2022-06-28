<?php

namespace Stickee\Laravel2fa\Exceptions;

use Exception;

/**
 * An exception to throw if the authentication is locked for a certain amount of time.
 * E.g. a user has attempted to authenticate too many times within a period of time.
 */
class AuthenticationLockException extends Exception
{
    public int $availableAt;

    /**
     * Constructor
     *
     * @param int $availableAt The timestamp when the next authentication is available.
     * @param null|string $message The exception message.
     * @param int $code The exception code.
     * @param null|Exception $previous The previous exception.
     */
    public function __construct(int $availableAt, string $message = null, $code = 0, Exception $previous = null)
    {
        $this->availableAt = $availableAt;

        if (is_null($message)) {
            $message = sprintf(
                'You have attempted to authenticate too many times recently. Please wait %d minutes before trying again.',
                ceil(($availableAt - time()) / 60)
            );
        }

        parent::__construct($message, $code, $previous);
    }
}
