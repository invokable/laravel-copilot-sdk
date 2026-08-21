<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Why no usable transport could be offered.
 *
 * @experimental
 */
enum CatalogUnavailableTransportReason: string
{
    /** The card advertises no transport this runtime can use. */
    case NoEligibleTransport = 'no-eligible-transport';

    /** Every advertised transport is of a kind this runtime does not implement. */
    case TransportNotSupported = 'transport-not-supported';

    /** Eligible remotes could not be enumerated. */
    case RemoteEnumerationUnavailable = 'remote-enumeration-unavailable';
}
