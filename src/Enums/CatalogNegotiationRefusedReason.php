<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Why capability and protocol-version negotiation refused a caller.
 *
 * @experimental
 */
enum CatalogNegotiationRefusedReason: string
{
    /** The caller's protocol version is below the lowest this runtime serves. */
    case UnsupportedProtocolVersion = 'unsupported-protocol-version';

    /** The caller requires at least one capability this runtime cannot honour. */
    case UnsupportedCapability = 'unsupported-capability';
}
