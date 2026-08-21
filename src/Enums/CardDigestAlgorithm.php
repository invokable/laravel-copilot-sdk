<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Hash algorithm used to compute a card digest.
 *
 * @experimental
 */
enum CardDigestAlgorithm: string
{
    /** SHA-256 over RFC 8785 canonical JSON encoded as UTF-8. */
    case Sha256Rfc8785 = 'sha256-rfc8785';
}
