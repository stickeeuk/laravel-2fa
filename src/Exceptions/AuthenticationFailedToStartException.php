<?php

namespace Stickee\Laravel2fa\Exceptions;

use Exception;

abstract class AuthenticationFailedToStartException extends Exception
{
    /**
     * Convert the values of the exception to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
