<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Memory injection mode for a session.
 */
enum MemoryMode: string
{
    case SystemPrompt = 'system-prompt';
    case UserPrompt = 'user-prompt';
}
