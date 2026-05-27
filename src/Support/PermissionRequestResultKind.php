<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

final readonly class PermissionRequestResultKind
{
    public const string APPROVE_ONCE = PermissionDecision::APPROVE_ONCE;

    public const string APPROVE_FOR_SESSION = PermissionDecision::APPROVE_FOR_SESSION;

    public const string APPROVE_FOR_LOCATION = PermissionDecision::APPROVE_FOR_LOCATION;

    public const string APPROVE_PERMANENTLY = PermissionDecision::APPROVE_PERMANENTLY;

    public const string REJECT = PermissionDecision::REJECT;

    public const string USER_NOT_AVAILABLE = PermissionDecision::USER_NOT_AVAILABLE;

    public const string NO_RESULT = PermissionDecision::NO_RESULT;

    /**
     * Approve the request once.
     */
    public static function approveOnce(): array
    {
        return PermissionDecision::approveOnce();
    }

    /**
     * Approve all requests from this session.
     */
    public static function approveForSession(): array
    {
        return PermissionDecision::approveForSession();
    }

    /**
     * Approve all requests from this location.
     */
    public static function approveForLocation(): array
    {
        return PermissionDecision::approveForLocation();
    }

    /**
     * Approve the request permanently (persisted across sessions).
     *
     * @param  string  $domain  The URL domain to approve permanently
     */
    public static function approvePermanently(string $domain): array
    {
        return PermissionDecision::approvePermanently($domain);
    }

    /**
     * Reject the permission request.
     */
    public static function reject(?string $feedback = null): array
    {
        return PermissionDecision::reject($feedback);
    }

    /**
     * The user is not available to handle the permission request.
     */
    public static function userNotAvailable(): array
    {
        return PermissionDecision::userNotAvailable();
    }

    /**
     * Return no-result to indicate the handler cannot provide a result.
     * When returned from a permission handler, the RPC call is skipped entirely.
     */
    public static function noResult(): array
    {
        return PermissionDecision::noResult();
    }

    /**
     * Array for Laravel\Prompts\select.
     * These are the choices available when responding to a permission request interactively.
     */
    public static function select(): array
    {
        return PermissionDecision::select();
    }
}
