<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * OAuth grant type for HTTP MCP server configuration.
 */
enum McpServerConfigHttpOauthGrantType: string
{
    case AUTHORIZATION_CODE = 'authorization_code';
    case CLIENT_CREDENTIALS = 'client_credentials';
}
