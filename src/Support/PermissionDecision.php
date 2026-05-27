<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

final readonly class PermissionDecision
{
    public const string APPROVE_ONCE = 'approve-once';

    public const string APPROVE_FOR_SESSION = 'approve-for-session';

    public const string APPROVE_FOR_LOCATION = 'approve-for-location';

    public const string APPROVE_PERMANENTLY = 'approve-permanently';

    public const string REJECT = 'reject';

    public const string USER_NOT_AVAILABLE = 'user-not-available';

    public const string NO_RESULT = 'no-result';

    public static function approveOnce(): array
    {
        return ['kind' => self::APPROVE_ONCE];
    }

    public static function approveForSession(): array
    {
        return ['kind' => self::APPROVE_FOR_SESSION];
    }

    public static function approveForLocation(): array
    {
        return ['kind' => self::APPROVE_FOR_LOCATION];
    }

    public static function approvePermanently(string $domain): array
    {
        return ['kind' => self::APPROVE_PERMANENTLY, 'domain' => $domain];
    }

    public static function reject(?string $feedback = null): array
    {
        return array_filter([
            'kind' => self::REJECT,
            'feedback' => $feedback,
        ], fn ($value) => $value !== null);
    }

    public static function userNotAvailable(): array
    {
        return ['kind' => self::USER_NOT_AVAILABLE];
    }

    public static function noResult(): array
    {
        return ['kind' => self::NO_RESULT];
    }

    /**
     * Array for Laravel\Prompts\select.
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
