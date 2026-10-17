<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Result variants for skill installation management RPCs.
 *
 * @experimental
 */
enum SkillInstallationManagementResultKind: string
{
    case InvalidRequest = 'invalid-request';
    case NegotiationRefused = 'negotiation-refused';
    case NetworkFailure = 'network-failure';
    case Outcome = 'outcome';
}
