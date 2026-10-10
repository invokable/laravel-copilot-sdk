<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderQuotaUnit: string
{
    case AI_CREDITS = 'ai_credits';
    case REQUESTS = 'requests';
    case TOKENS = 'tokens';
    case UNKNOWN = 'unknown';
}
