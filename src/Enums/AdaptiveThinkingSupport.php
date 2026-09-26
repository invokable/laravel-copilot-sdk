<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Adaptive-thinking support reported for a model.
 *
 * @experimental
 */
enum AdaptiveThinkingSupport: string
{
    case UNSUPPORTED = 'unsupported';
    case OPTIONAL = 'optional';
    case REQUIRED = 'required';
    case ADAPTIVE_ONLY = 'adaptive_only';
}
