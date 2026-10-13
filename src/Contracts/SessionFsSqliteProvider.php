<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Enums\SessionFSSqliteQueryType;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryResult;

/**
 * SQLite operations for the per-session database.
 *
 * Errors thrown from these methods are reported to the runtime as JSON-RPC errors
 * (no SessionFs error-code mapping is applied to SQL failures).
 */
interface SessionFsSqliteProvider
{
    /**
     * Execute a SQLite query against the per-session database.
     *
     * @param  array<string, float|int|string|null>|null  $params  Optional named bind parameters
     * @return SessionFsSqliteQueryResult|array{columns?: list<string>, rows?: list<array<string, mixed>>, rowsAffected?: int, lastInsertRowid?: int|float}|null
     */
    public function query(SessionFSSqliteQueryType $queryType, string $query, ?array $params = null): SessionFsSqliteQueryResult|array|null;

    /**
     * Check whether the per-session database already exists, without creating it.
     */
    public function exists(): bool;
}
