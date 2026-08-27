<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Client surface that submitted a permission response.
 */
enum PermissionDecisionSurface: string
{
    /** The interactive Copilot CLI terminal UI. */
    case TUI = 'tui';

    /** The non-interactive Copilot CLI prompt mode. */
    case PROMPT_MODE = 'prompt_mode';

    /** The Copilot App client. */
    case COPILOT_APP = 'copilot_app';

    /** An Agent Client Protocol host. */
    case ACP = 'acp';

    /** A generic Copilot SDK client. */
    case SDK = 'sdk';
}
