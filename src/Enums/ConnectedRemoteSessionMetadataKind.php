<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Neutral SDK discriminator for the connected remote session kind.
 */
enum ConnectedRemoteSessionMetadataKind: string
{
    case CodingAgent = 'coding-agent';
    case RemoteSession = 'remote-session';
}
