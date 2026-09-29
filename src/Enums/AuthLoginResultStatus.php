<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum AuthLoginResultStatus: string
{
    case COMPLETED = 'completed';
    case DECLINED = 'declined';
    case NEEDS_PLAINTEXT_CONSENT = 'needs-plaintext-consent';
}
