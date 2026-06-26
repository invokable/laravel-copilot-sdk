<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Provider transport for provider config.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum ProviderConfigTransport: string
{
    /** HTTP request/streaming transport. */
    case Http = 'http';

    /** WebSocket transport. */
    case Websockets = 'websockets';
}
