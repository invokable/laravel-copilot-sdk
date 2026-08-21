<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Transport exposed by a remote endpoint.
 *
 * @experimental
 */
enum McpPlanRemoteTransport: string
{
    /** An HTTP endpoint. */
    case Http = 'http';

    /** A streamable HTTP endpoint. */
    case StreamableHttp = 'streamable-http';

    /** A server-sent events endpoint. */
    case Sse = 'sse';
}
