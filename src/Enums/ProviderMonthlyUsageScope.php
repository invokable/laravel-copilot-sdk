<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderMonthlyUsageScope: string
{
    case USER = 'user';
    case UNKNOWN = 'unknown';
}
