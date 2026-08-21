<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Why the catalog authority did not accept the caller's identity.
 *
 * @experimental
 */
enum CatalogAuthenticationRequiredReason: string
{
    /** No credential was presented. */
    case NoCredential = 'no-credential';

    /** A credential was presented and its lifetime has elapsed. */
    case CredentialExpired = 'credential-expired';

    /** A credential was presented and the authority refused it. */
    case CredentialRejected = 'credential-rejected';
}
