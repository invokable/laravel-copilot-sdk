<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum AiCreditsStatus: string
{
    case COMPLETE = 'complete';
    case PARTIAL = 'partial';
    case UNAVAILABLE = 'unavailable';
}
