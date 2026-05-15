<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Per-session remote mode.
 * "off" disables remote, "export" exports session events to GitHub without enabling remote steering,
 * "on" enables both export and remote steering.
 */
enum RemoteSessionMode: string
{
    case Off = 'off';
    case Export = 'export';
    case On = 'on';
}
