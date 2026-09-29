<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum AuthLoginStepKind: string
{
    case OPEN_URL = 'open-url';
    case INPUT_REQUIRED = 'input-required';
    case AWAITING = 'awaiting';
    case NEEDS_INTERACTION = 'needs-interaction';
    case COMPLETED = 'completed';
    case ERROR = 'error';
}
