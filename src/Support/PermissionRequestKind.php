<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

/**
 * The other language versions are only defined as strings.
 * Make it easy to use with static methods.
 */
class PermissionRequestKind
{
    public static function approved(): array
    {
        return ['kind' => 'approved'];
    }

    public static function deniedByRules(): array
    {
        return ['kind' => 'denied-by-rules'];
    }

    public static function deniedNoApprovalRuleAndCouldNotRequestFromUser(): array
    {
        return ['kind' => 'denied-no-approval-rule-and-could-not-request-from-user'];
    }

    public static function deniedInteractivelyByUser(): array
    {
        return ['kind' => 'denied-interactively-by-user'];
    }

    /**
     * Array for Laravel\Prompts\select.
     */
    public static function select(): array
    {
        return [
            'approved' => __('Approved'),
            'denied-by-rules' => __('Denied by Rules'),
            'denied-no-approval-rule-and-could-not-request-from-user' => __('Denied No Approval Rule and Could Not Request from User'),
            'denied-interactively-by-user' => __('Denied Interactively by User'),
        ];
    }
}
