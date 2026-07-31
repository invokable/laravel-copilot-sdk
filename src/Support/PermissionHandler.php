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
     * Throws when managed settings are enabled for the session, since managed
     * policy requires an explicit user decision in that case. When the request
     * carries `managedApprovalRequired`, returns `no-result` so the runtime's
     * default (fail-closed) handling takes over instead of auto-approving.
     *
     * @return Closure(array, array): array
     *
     * @throws \RuntimeException When managed settings are enabled for the session.
     */
    public static function approveAll(): Closure
    {
        return function (array $request, array $invocation): array {
            if (($invocation['managedSettingsEnabled'] ?? false) === true) {
                throw new \RuntimeException('approveAll cannot be used when managed settings are enabled.');
            }

            if (($request['managedApprovalRequired'] ?? false) === true) {
                return PermissionDecision::noResult();
            }

            return PermissionDecision::approveOnce();
        };
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
                'shell', 'write' => PermissionDecision::reject(),
                default => PermissionDecision::approveOnce(),
            };
        };
    }

    /**
     * Deny all permission requests.
     *
     * @return Closure(array, array): array
     */
    public static function denyAll(): Closure
    {
        return fn (array $request, array $invocation): array => PermissionDecision::reject();
    }
}
