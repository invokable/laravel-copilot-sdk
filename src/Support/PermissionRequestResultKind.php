<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

final readonly class PermissionRequestResultKind
{
    public const string APPROVED = 'approved';

    public const string APPROVE_ONCE = 'approve-once';

    public const string NO_RESULT = 'no-result';

    public const string DENIED_BY_RULES = 'denied-by-rules';

    public const string DENIED_NO_APPROVAL_RULE_AND_COULD_NOT_REQUEST_FROM_USER = 'denied-no-approval-rule-and-could-not-request-from-user';

    public const string USER_NOT_AVAILABLE = 'user-not-available';

    public const string DENIED_INTERACTIVELY_BY_USER = 'denied-interactively-by-user';

    public const string DENIED_BY_CONTENT_EXCLUSION_POLICY = 'denied-by-content-exclusion-policy';

    public const string DENIED_BY_PERMISSION_REQUEST_HOOK = 'denied-by-permission-request-hook';

    public static function approved(): array
    {
        return ['kind' => self::APPROVED];
    }

    /**
     * Approve the request once (preferred over approved() for one-time approvals).
     */
    public static function approveOnce(): array
    {
        return ['kind' => self::APPROVE_ONCE];
    }

    /**
     * Return no-result to indicate the handler cannot provide a result.
     * When returned from a permission handler, the RPC call is skipped entirely.
     */
    public static function noResult(): array
    {
        return ['kind' => self::NO_RESULT];
    }

    public static function deniedByRules(): array
    {
        return ['kind' => self::DENIED_BY_RULES];
    }

    public static function deniedNoApprovalRuleAndCouldNotRequestFromUser(): array
    {
        return ['kind' => self::DENIED_NO_APPROVAL_RULE_AND_COULD_NOT_REQUEST_FROM_USER];
    }

    /**
     * The user is not available to handle the permission request.
     */
    public static function userNotAvailable(): array
    {
        return ['kind' => self::USER_NOT_AVAILABLE];
    }

    public static function deniedInteractivelyByUser(?string $feedback = null): array
    {
        $result = ['kind' => self::DENIED_INTERACTIVELY_BY_USER];

        if ($feedback !== null) {
            $result['feedback'] = $feedback;
        }

        return $result;
    }

    public static function deniedByContentExclusionPolicy(string $path, string $message): array
    {
        return [
            'kind' => self::DENIED_BY_CONTENT_EXCLUSION_POLICY,
            'path' => $path,
            'message' => $message,
        ];
    }

    public static function deniedByPermissionRequestHook(?string $message = null, ?bool $interrupt = null): array
    {
        $result = ['kind' => self::DENIED_BY_PERMISSION_REQUEST_HOOK];

        if ($message !== null) {
            $result['message'] = $message;
        }

        if ($interrupt !== null) {
            $result['interrupt'] = $interrupt;
        }

        return $result;
    }

    /**
     * Array for Laravel\Prompts\select.
     */
    public static function select(): array
    {
        return [
            self::APPROVED => __('Approved'),
            self::APPROVE_ONCE => __('Approve Once'),
            self::DENIED_BY_RULES => __('Denied by Rules'),
            self::DENIED_NO_APPROVAL_RULE_AND_COULD_NOT_REQUEST_FROM_USER => __('Denied No Approval Rule and Could Not Request from User'),
            self::USER_NOT_AVAILABLE => __('User Not Available'),
            self::DENIED_INTERACTIVELY_BY_USER => __('Denied Interactively by User'),
            self::DENIED_BY_CONTENT_EXCLUSION_POLICY => __('Denied by Content Exclusion Policy'),
            self::DENIED_BY_PERMISSION_REQUEST_HOOK => __('Denied by Permission Request Hook'),
        ];
    }
}
