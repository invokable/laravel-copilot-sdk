<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Which authority produced a policy decision.
 *
 * @experimental
 */
enum McpPlanPolicySource: string
{
    /** No policy applied, so the server is permitted by default. */
    case None = 'none';

    /** An enterprise allowlist evaluated the server. */
    case EnterpriseAllowlist = 'enterprise-allowlist';

    /** The registry the card came from evaluated the server. */
    case RegistryPolicy = 'registry-policy';

    /** Local trust settings evaluated the server. */
    case LocalTrust = 'local-trust';
}
