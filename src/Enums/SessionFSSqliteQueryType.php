<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * How to execute a SQLite query.
 *
 * - `exec`: DDL/multi-statement queries that produce no result rows.
 * - `query`: SELECT queries that return rows.
 * - `run`: INSERT/UPDATE/DELETE queries that return rowsAffected.
 */
enum SessionFSSqliteQueryType: string
{
    case Exec = 'exec';
    case Query = 'query';
    case Run = 'run';
}
