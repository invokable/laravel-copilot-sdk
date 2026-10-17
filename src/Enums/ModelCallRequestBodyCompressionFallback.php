<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ModelCallRequestBodyCompressionFallback: string
{
    case RETRIED = 'retried';
    case DISABLED = 'disabled';
}
