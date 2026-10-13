<?php

declare(strict_types=1);

namespace Revolution\Copilot\Exceptions;

use Revolution\Copilot\Enums\SessionFSSqliteTransactionErrorClass;
use RuntimeException;

/**
 * Throw from a SessionFs SQLite provider's `transaction()` to classify the failure.
 *
 * Any other thrown value is reported as `fatal`. Use `busyOrLocked` only when SQLite
 * reported BUSY/LOCKED before commit and the transaction was rolled back.
 */
class SessionFsSqliteTransactionFailure extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly SessionFSSqliteTransactionErrorClass $errorClass = SessionFSSqliteTransactionErrorClass::Fatal,
    ) {
        parent::__construct($message);
    }
}
