<?php

declare(strict_types=1);

namespace Revolution\Copilot\Exceptions;

use Exception;
use Throwable;

/**
 * JSON-RPC error response exception.
 */
class JsonRpcException extends Exception
{
    protected string $rpcMessage;

    public function __construct(
        public $code,
        string $message,
        public readonly mixed $data = null,
        ?Throwable $previous = null,
    ) {
        $this->rpcMessage = $message;

        parent::__construct("JSON-RPC Error {$code}: {$message}", 0, $previous);
    }

    public function withDiagnostic(string $diagnostic): self
    {
        return new self($this->code, $this->rpcMessage."\n".$diagnostic, $this->data, $this);
    }
}
