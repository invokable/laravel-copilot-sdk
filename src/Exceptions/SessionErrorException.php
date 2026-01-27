<?php

declare(strict_types=1);

namespace Revolution\Copilot\Exceptions;

use RuntimeException;

class SessionErrorException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct("Session error: $message");
    }
}
