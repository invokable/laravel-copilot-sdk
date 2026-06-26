<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * OAuth grant type override for MCP OAuth login.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum McpOauthLoginGrantType: string
{
    /** Interactive browser-based OAuth flow using an authorization code, typically with PKCE. */
    case AuthorizationCode = 'authorization_code';

    /** Headless OAuth flow where a confidential client authenticates directly with a client secret. */
    case ClientCredentials = 'client_credentials';
}
