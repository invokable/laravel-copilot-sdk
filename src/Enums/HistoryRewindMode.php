<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Scope of a rewind operation.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum HistoryRewindMode: string
{
    case CONVERSATION = 'conversation';
    case CONVERSATION_AND_FILES = 'conversation-and-files';
}
