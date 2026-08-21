<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Controlled reason or actor responsible for a permission response.
 */
enum PermissionDecisionSource: string
{
    /** The response followed the assisted-approval judge recommendation. */
    case ASSISTED_APPROVAL = 'assisted_approval';

    /** A human supplied the response through an interactive prompt. */
    case HUMAN_RESPONSE = 'human_response';

    /** The host applied a standing policy or override rather than a judge recommendation or human decision. */
    case HOST_POLICY = 'host_policy';

    /** The host denied the request because no interactive user response was available. */
    case UNATTENDED_FALLBACK = 'unattended_fallback';
}
