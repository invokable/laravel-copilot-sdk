<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Whether authored agent models are advisory preferences or required constraints.
 */
enum AgentModelPolicy: string
{
    case PREFERRED = 'preferred';
    case REQUIRED = 'required';
}
