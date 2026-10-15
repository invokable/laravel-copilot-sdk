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
     * policy requires an explicit user decision in that case. When a supported
     * prompt context indicates that a human response is required, returns
     * `no-result`; older runtimes fall back to the raw request marker.
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

            $promptRequest = $invocation['promptRequest'] ?? null;
            $promptKind = is_array($promptRequest) ? ($promptRequest['kind'] ?? null) : null;
            $promptManagedApprovalRequired = is_array($promptRequest)
                ? ($promptRequest['managedApprovalRequired'] ?? null)
                : null;

            $managedApprovalRequired = ($request['managedApprovalRequired'] ?? false) === true;

            if (
                in_array($promptKind, ['commands', 'read', 'write', 'url', 'workflow'], true)
                && is_bool($promptManagedApprovalRequired)
            ) {
                $managedApprovalRequired = $promptManagedApprovalRequired;
            }

            if ($managedApprovalRequired) {
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
