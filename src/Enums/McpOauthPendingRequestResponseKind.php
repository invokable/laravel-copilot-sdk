<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum McpOauthPendingRequestResponseKind: string
{
    case Cancelled = 'cancelled';
    case Token = 'token';
}
