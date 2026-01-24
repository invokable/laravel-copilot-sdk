<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

/**
 * The other language versions are only defined as strings.
 * Make it easy to use with static methods.
 */
final readonly class PermissionRequestKind
{
    public const string APPROVED = 'approved';
    public const string DENIED_BY_RULES = 'denied-by-rules';
    public const string DENIED_NO_APPROVAL_RULE_AND_COULD_NOT_REQUEST_FROM_USER = 'denied-no-approval-rule-and-could-not-request-from-user';
    public const string DENIED_INTERACTIVELY_BY_USER = 'denied-interactively-by-user';

    public static function approved(): array
    {
        return ['kind' => self::APPROVED];
    }

    public static function deniedByRules(): array
    {
        return ['kind' => self::DENIED_BY_RULES];
    }

    public static function deniedNoApprovalRuleAndCouldNotRequestFromUser(): array
    {
        return ['kind' => self::DENIED_NO_APPROVAL_RULE_AND_COULD_NOT_REQUEST_FROM_USER];
    }

    public static function deniedInteractivelyByUser(): array
    {
        return ['kind' => self::DENIED_INTERACTIVELY_BY_USER];
    }

    /**
     * Array for Laravel\Prompts\select.
     */
    public static function select(): array
    {
        return [
            self::APPROVED => __('Approved'),
            self::DENIED_BY_RULES => __('Denied by Rules'),
            self::DENIED_NO_APPROVAL_RULE_AND_COULD_NOT_REQUEST_FROM_USER => __('Denied No Approval Rule and Could Not Request from User'),
            self::DENIED_INTERACTIVELY_BY_USER => __('Denied Interactively by User'),
        ];
    }
}
