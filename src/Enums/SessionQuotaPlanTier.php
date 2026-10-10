<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum SessionQuotaPlanTier: string
{
    case FREE = 'free';
    case EDU = 'edu';
    case PRO = 'pro';
    case PRO_PLUS = 'pro_plus';
    case BUSINESS = 'business';
    case ENTERPRISE = 'enterprise';
    case MAX = 'max';
    case UNKNOWN = 'unknown';
}
