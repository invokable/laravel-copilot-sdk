<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

enum UIExitPlanModeAction: string
{
    case ExitOnly = 'exit_only';
    case Interactive = 'interactive';
    case Autopilot = 'autopilot';
    case AutopilotFleet = 'autopilot_fleet';
}
