<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

final readonly class PermissionRequestResultKind
{
    public const string APPROVE_ONCE = 'approve-once';

    public const string APPROVE_FOR_SESSION = 'approve-for-session';

    public const string APPROVE_FOR_LOCATION = 'approve-for-location';

    public const string REJECT = 'reject';

    public const string USER_NOT_AVAILABLE = 'user-not-available';

    public const string NO_RESULT = 'no-result';

    /**
     * Approve the request once.
     */
    public static function approveOnce(): array
    {
        return ['kind' => self::APPROVE_ONCE];
    }

    /**
     * Approve all requests from this session.
     */
    public static function approveForSession(): array
    {
        return ['kind' => self::APPROVE_FOR_SESSION];
    }

    /**
     * Approve all requests from this location.
     */
    public static function approveForLocation(): array
    {
        return ['kind' => self::APPROVE_FOR_LOCATION];
    }

    /**
     * Reject the permission request.
     */
    public static function reject(): array
    {
        return ['kind' => self::REJECT];
    }

    /**
     * The user is not available to handle the permission request.
     */
    public static function userNotAvailable(): array
    {
        return ['kind' => self::USER_NOT_AVAILABLE];
    }

    /**
     * Return no-result to indicate the handler cannot provide a result.
     * When returned from a permission handler, the RPC call is skipped entirely.
     */
    public static function noResult(): array
    {
        return ['kind' => self::NO_RESULT];
    }

    /**
     * Array for Laravel\Prompts\select.
     * These are the choices available when responding to a permission request interactively.
     */
    public static function select(): array
    {
        return [
            self::APPROVE_ONCE => __('Approve Once'),
            self::APPROVE_FOR_SESSION => __('Approve for Session'),
            self::APPROVE_FOR_LOCATION => __('Approve for Location'),
            self::REJECT => __('Reject'),
        ];
    }
}
