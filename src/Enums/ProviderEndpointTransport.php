<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Transport to be used for provider endpoint requests.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum ProviderEndpointTransport: string
{
    /** HTTP request/streaming transport. */
    case Http = 'http';

    /** WebSocket transport. */
    case Websockets = 'websockets';
}
