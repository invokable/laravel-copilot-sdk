<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderQuotaQuantityKind: string
{
    case AUTHORITATIVE_BUDGET = 'authoritative_budget';
    case ADVISORY_BALANCE = 'advisory_balance';
    case NONE = 'none';
}
