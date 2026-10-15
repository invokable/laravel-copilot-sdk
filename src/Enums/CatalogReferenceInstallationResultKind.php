<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Top-level result variants for catalog reference installation planning.
 *
 * @experimental
 */
enum CatalogReferenceInstallationResultKind: string
{
    case InvalidRequest = 'invalid-request';
    case NegotiationRefused = 'negotiation-refused';
    case Outcome = 'outcome';
}
