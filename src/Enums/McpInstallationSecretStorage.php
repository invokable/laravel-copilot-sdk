<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Secret storage backend used for an MCP installation.
 *
 * @experimental
 */
enum McpInstallationSecretStorage: string
{
    case Keychain = 'keychain';

    case PrivateFile = 'private-file';
}
