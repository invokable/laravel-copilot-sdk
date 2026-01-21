<?php

declare(strict_types=1);

namespace Revolution\Copilot\Exceptions;

use RuntimeException;

class StrayRequestException extends RuntimeException
{
    public function __construct(string $method)
    {
        parent::__construct('Attempted request to ['.$method.'] without a matching fake.');
    }
}
