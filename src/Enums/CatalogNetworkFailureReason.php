<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Categorised network failure reason.
 *
 * @experimental
 */
enum CatalogNetworkFailureReason: string
{
    /** No network is available. */
    case Offline = 'offline';

    /** The authority's name could not be resolved. */
    case Dns = 'dns';

    /** The request exceeded its time budget. */
    case Timeout = 'timeout';

    /** The TLS handshake or certificate validation failed. */
    case Tls = 'tls';

    /** The connection was refused or reset. */
    case ConnectionRefused = 'connection-refused';

    /** The authority returned a status the runtime treats as a failure. */
    case HttpStatus = 'http-status';

    /** The response exceeded the permitted size. */
    case ResponseTooLarge = 'response-too-large';

    /** A redirect was refused by the runtime's redirect policy. */
    case RedirectRejected = 'redirect-rejected';
}
