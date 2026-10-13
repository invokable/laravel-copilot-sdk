<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

/**
 * Marks a SessionFs provider as supporting the per-session SQLite database.
 *
 * Required when the client `sessionFs` configuration declares `capabilities.sqlite`.
 */
interface SessionFsSqliteCapableProvider extends SessionFsProvider
{
    public function sqlite(): SessionFsSqliteProvider;
}
