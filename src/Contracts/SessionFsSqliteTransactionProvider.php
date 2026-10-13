<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Exceptions\SessionFsSqliteTransactionFailure;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionStatement;

/**
 * Adds atomic multi-statement transactions to a SessionFs SQLite provider.
 */
interface SessionFsSqliteTransactionProvider extends SessionFsSqliteProvider
{
    /**
     * Execute the statements atomically against the per-session database.
     *
     * Roll back the whole batch if any statement fails. Throw
     * {@see SessionFsSqliteTransactionFailure} to classify
     * the failure; any other exception is reported as `fatal`.
     *
     * @param  list<SessionFsSqliteTransactionStatement>  $statements
     * @return list<SessionFsSqliteQueryResult|array> One result per statement, in the same order
     */
    public function transaction(array $statements): array;
}
