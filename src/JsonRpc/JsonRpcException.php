<?php

declare(strict_types=1);

namespace Revolution\Copilot\JsonRpc;

use Exception;

/**
 * JSON-RPC error response exception.
 */
class JsonRpcException extends Exception
{
    public function __construct(
        public readonly int $code,
        string $message,
        public readonly mixed $data = null,
    ) {
        parent::__construct("JSON-RPC Error {$code}: {$message}");
    }
}
