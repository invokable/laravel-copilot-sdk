<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * SQLite transaction failure classification.
 *
 * - `busyOrLocked`: SQLite reported BUSY or LOCKED before commit; the transaction was rolled back and may be retried.
 * - `fatal`: The statement, database, or provider failed definitively and must not be retried automatically.
 * - `postCommitAmbiguous`: The transport failed after the provider may have committed; retrying could duplicate effects.
 */
enum SessionFSSqliteTransactionErrorClass: string
{
    case BusyOrLocked = 'busyOrLocked';
    case Fatal = 'fatal';
    case PostCommitAmbiguous = 'postCommitAmbiguous';
}
