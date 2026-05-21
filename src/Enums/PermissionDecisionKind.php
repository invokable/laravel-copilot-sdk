<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Permission decision/result kind values.
 */
enum PermissionDecisionKind: string
{
    case APPROVED = 'approved';
    case APPROVED_FOR_LOCATION = 'approved-for-location';
    case APPROVED_FOR_SESSION = 'approved-for-session';
    case APPROVE_FOR_LOCATION = 'approve-for-location';
    case APPROVE_FOR_SESSION = 'approve-for-session';
    case APPROVE_ONCE = 'approve-once';
    case APPROVE_PERMANENTLY = 'approve-permanently';
    case CANCELLED = 'cancelled';
    case DENIED_BY_CONTENT_EXCLUSION_POLICY = 'denied-by-content-exclusion-policy';
    case DENIED_BY_PERMISSION_REQUEST_HOOK = 'denied-by-permission-request-hook';
    case DENIED_BY_RULES = 'denied-by-rules';
    case DENIED_INTERACTIVELY_BY_USER = 'denied-interactively-by-user';
    case DENIED_NO_APPROVAL_RULE_AND_COULD_NOT_REQUEST_FROM_USER = 'denied-no-approval-rule-and-could-not-request-from-user';
    case REJECT = 'reject';
    case USER_NOT_AVAILABLE = 'user-not-available';
}
