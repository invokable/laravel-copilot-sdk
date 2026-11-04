<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum GitHubFetchResultKind: string
{
    case Response = 'response';
    case CircuitBreaker = 'circuit-breaker';
    case ProxyResponse = 'proxy-response';
    case Failed = 'failed';
}
