<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Current lifecycle status for an Autopilot objective.
 */
enum AutopilotObjectiveStatus: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
}
