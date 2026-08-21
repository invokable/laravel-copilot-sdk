<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Which hardened-fetch control refused a retrieval.
 *
 * @experimental
 */
enum CatalogUnsafeRetrievalReason: string
{
    /** The URL used a scheme the runtime refuses to fetch. */
    case BlockedScheme = 'blocked-scheme';

    /** The URL embedded credentials. */
    case CredentialsInUrl = 'credentials-in-url';

    /** The URL resolved to a loopback, private, link-local, or cloud metadata address. */
    case BlockedAddress = 'blocked-address';

    /** A redirect target resolved to a blocked address. */
    case RedirectToBlockedAddress = 'redirect-to-blocked-address';

    /** The configured proxy policy refused the request. */
    case ProxyRejected = 'proxy-rejected';

    /** The authority is not permitted for card retrieval. */
    case HostNotPermitted = 'host-not-permitted';
}
