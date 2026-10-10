<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderMonthlyUsageState: string
{
    case AVAILABLE = 'available';
    case NO_POLICY = 'no_policy';
    case UNAVAILABLE = 'unavailable';
    case UNKNOWN = 'unknown';
}
