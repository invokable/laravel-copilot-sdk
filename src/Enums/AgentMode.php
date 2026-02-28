<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Agent mode for the Copilot session.
 */
enum AgentMode: string
{
    case AUTOPILOT = 'autopilot';
    case INTERACTIVE = 'interactive';
    case PLAN = 'plan';
    case SHELL = 'shell';
}
