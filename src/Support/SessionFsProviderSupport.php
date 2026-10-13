<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Revolution\Copilot\Contracts\SessionFsBinaryProvider;
use Revolution\Copilot\Contracts\SessionFsFilesystemProvider;
use Revolution\Copilot\Contracts\SessionFsProvider;
use Revolution\Copilot\Contracts\SessionFsSqliteCapableProvider;
use Revolution\Copilot\Contracts\SessionFsSqliteProvider;
use Revolution\Copilot\Contracts\SessionFsSqliteTransactionProvider;

/**
 * Capability detection for SessionFs providers, which can be objects implementing the
 * optional SessionFs interfaces or arrays of callables keyed by method name.
 *
 * @internal
 */
final class SessionFsProviderSupport
{
    /**
     * Resolve a callable for one of the extended filesystem methods
     * (appendFile, exists, stat, mkdir, readdir, readdirWithTypes, rm, rename).
     */
    public static function filesystemMethod(SessionFsProvider|array $provider, string $method): ?callable
    {
        if ($provider instanceof SessionFsProvider) {
            return $provider instanceof SessionFsFilesystemProvider ? [$provider, $method] : null;
        }

        $callable = $provider[$method] ?? null;

        return is_callable($callable) ? $callable : null;
    }

    /**
     * Resolve the SQLite provider, if the provider supports SQLite.
     *
     * @return SessionFsSqliteProvider|array{query: callable, exists: callable, transaction?: callable}|null
     */
    public static function sqlite(SessionFsProvider|array $provider): SessionFsSqliteProvider|array|null
    {
        if ($provider instanceof SessionFsProvider) {
            return $provider instanceof SessionFsSqliteCapableProvider ? $provider->sqlite() : null;
        }

        $sqlite = $provider['sqlite'] ?? null;
        if ($sqlite instanceof SessionFsSqliteProvider) {
            return $sqlite;
        }

        return is_array($sqlite) && is_callable($sqlite['query'] ?? null) && is_callable($sqlite['exists'] ?? null)
            ? $sqlite
            : null;
    }

    /**
     * Resolve a callable that runs a SQLite transaction, if supported.
     */
    public static function sqliteTransaction(SessionFsSqliteProvider|array $sqlite): ?callable
    {
        if ($sqlite instanceof SessionFsSqliteProvider) {
            return $sqlite instanceof SessionFsSqliteTransactionProvider ? [$sqlite, 'transaction'] : null;
        }

        return is_callable($sqlite['transaction'] ?? null) ? $sqlite['transaction'] : null;
    }

    public static function supportsSqlite(SessionFsProvider|array $provider): bool
    {
        return self::sqlite($provider) !== null;
    }

    public static function supportsBinary(SessionFsProvider|array $provider): bool
    {
        return $provider instanceof SessionFsProvider
            ? $provider instanceof SessionFsBinaryProvider
            : is_callable($provider['readFileBytes'] ?? null) && is_callable($provider['writeFileBytes'] ?? null);
    }
}
