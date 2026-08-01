<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Agent mode a queued send item runs in.
 */
enum SendAgentMode: string
{
    case INTERACTIVE = 'interactive';
    case PLAN = 'plan';
    case AUTOPILOT = 'autopilot';
    case SHELL = 'shell';
}
