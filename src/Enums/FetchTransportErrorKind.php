<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum FetchTransportErrorKind: string
{
    case Request = 'request';
    case Timeout = 'timeout';
    case Tunnel = 'tunnel';
    case ProxyAuthentication = 'proxy_authentication';
    case RequestConfiguration = 'request_configuration';
    case ResponseBodyLimit = 'response_body_limit';
    case Redirect = 'redirect';
    case Canceled = 'canceled';
    case StreamClosed = 'stream_closed';
}
