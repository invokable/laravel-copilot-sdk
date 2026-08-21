<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * What policy decided for a planned server.
 *
 * @experimental
 */
enum McpPlanPolicyDecision: string
{
    /** Policy permits the server. */
    case Allowed = 'allowed';

    /** Policy forbids the server, so the plan cannot be applied. */
    case Blocked = 'blocked';

    /** Policy permits the server only after an explicit approval. */
    case RequiresApproval = 'requires-approval';
}
