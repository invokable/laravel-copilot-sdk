<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ModelCallWebSocketFallbackErrorKind: string
{
    case DNS = 'dns';
    case TLS = 'tls';
    case TIMEOUT = 'timeout';
    case CONNECTION_REFUSED = 'connection_refused';
    case CONNECTION_RESET = 'connection_reset';
    case CLOSED_BY_PEER = 'closed_by_peer';
    case CLOSED_LOCALLY = 'closed_locally';
    case NOT_CONNECTED = 'not_connected';
    case HTTP_STATUS = 'http_status';
    case PROTOCOL = 'protocol';
    case CONFIGURATION = 'configuration';
    case OTHER = 'other';
}
