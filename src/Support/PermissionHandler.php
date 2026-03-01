<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Closure;

/**
 * Provides pre-built permission request handlers.
 */
final readonly class PermissionHandler
{
    /**
     * A handler that approves all permission requests.
     *
     * @return Closure(array, array): array
     */
    public static function approveAll(): Closure
    {
        return fn (array $request, array $invocation): array => PermissionRequestKind::approved();
    }

    /**
     * A handler that approves all permission requests except for shell and write.
     *
     * @return Closure(array, array): array
     */
    public static function approveSafety(): Closure
    {
        return function (array $request, array $invocation): array {
            return match ($request['kind'] ?? '') {
                'shell', 'write' => PermissionRequestKind::deniedInteractivelyByUser(),
                default => PermissionRequestKind::approved(),
            };
        };
    }
}
