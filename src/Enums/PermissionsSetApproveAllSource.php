<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Source for approve-all telemetry.
 */
enum PermissionsSetApproveAllSource: string
{
    case AUTOPILOT_CONFIRMATION = 'autopilot_confirmation';
    case CLI_FLAG = 'cli_flag';
    case RPC = 'rpc';
    case SLASH_COMMAND = 'slash_command';
}
