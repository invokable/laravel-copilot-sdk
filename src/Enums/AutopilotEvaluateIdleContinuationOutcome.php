<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Outcome of evaluating the session-idle Autopilot continuation policy.
 *
 * @internal
 */
enum AutopilotEvaluateIdleContinuationOutcome: string
{
    case Continued = 'continued';
    case Stopped = 'stopped';
    case NotApplicable = 'notApplicable';
}
