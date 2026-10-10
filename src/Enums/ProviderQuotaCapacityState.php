<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderQuotaCapacityState: string
{
    case AVAILABLE = 'available';
    case EXHAUSTED = 'exhausted';
    case UNLIMITED = 'unlimited';
    case NOT_REQUIRED = 'not_required';
    case NOT_APPLICABLE = 'not_applicable';
    case UNKNOWN = 'unknown';
    case UNAVAILABLE = 'unavailable';
}
