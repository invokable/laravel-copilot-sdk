<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Connection state for the Copilot client.
 */
enum ConnectionState: string
{
    case DISCONNECTED = 'disconnected';
    case CONNECTING = 'connecting';
    case CONNECTED = 'connected';
    case ERROR = 'error';
}
