<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Source for permission-mode telemetry. Defaults to `rpc` when omitted for SDK callers.
 *
 * @experimental
 */
enum PermissionModeSource: string
{
    /** The mode was set from a CLI command-line flag. */
    case CLI_FLAG = 'cli_flag';

    /** The mode was set by a slash command. */
    case SLASH_COMMAND = 'slash_command';

    /** The mode was set by confirming autopilot behavior. */
    case AUTOPILOT_CONFIRMATION = 'autopilot_confirmation';

    /** The mode was set at startup by the `defaultPermissionMode` user setting. */
    case USER_SETTING = 'user_setting';

    /** The mode was set through an RPC caller. */
    case RPC = 'rpc';
}
