<?php

declare(strict_types=1);

namespace Revolution\Copilot\Exceptions;

use RuntimeException;

class SessionTimeoutException extends RuntimeException
{
    public function __construct(float $timeout)
    {
        parent::__construct("Timeout after {$timeout}s waiting for session.idle");
    }
}
