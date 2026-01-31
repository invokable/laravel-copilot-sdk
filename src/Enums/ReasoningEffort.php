<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Valid reasoning effort levels for models that support it.
 *
 * This controls the computational effort level that models use when generating responses.
 * Only valid for models where capabilities.supports.reasoningEffort is true.
 */
enum ReasoningEffort: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case XHIGH = 'xhigh';
}
