<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderQuotaAccessState: string
{
    case ALLOWED = 'allowed';
    case DENIED = 'denied';
    case NOT_REQUIRED = 'not_required';
    case UNKNOWN = 'unknown';
    case UNAVAILABLE = 'unavailable';
}
