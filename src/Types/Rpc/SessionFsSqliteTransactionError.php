<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SessionFSSqliteTransactionErrorClass;

/**
 * Classified SQLite transaction failure. `busyOrLocked` guarantees rollback;
 * `postCommitAmbiguous` must never be retried.
 */
readonly class SessionFsSqliteTransactionError implements Arrayable
{
    /**
     * @param  SessionFSSqliteTransactionErrorClass|string  $errorClass  Failure classification
     * @param  string  $message  Human-readable transaction failure message
     */
    public function __construct(
        public SessionFSSqliteTransactionErrorClass|string $errorClass,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        $errorClass = $data['errorClass'] ?? SessionFSSqliteTransactionErrorClass::Fatal->value;

        return new self(
            errorClass: is_string($errorClass)
                ? (SessionFSSqliteTransactionErrorClass::tryFrom($errorClass) ?? $errorClass)
                : SessionFSSqliteTransactionErrorClass::Fatal,
            message: Arr::string($data, 'message', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'errorClass' => $this->errorClass instanceof SessionFSSqliteTransactionErrorClass
                ? $this->errorClass->value
                : $this->errorClass,
            'message' => $this->message,
        ];
    }
}
