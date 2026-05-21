<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Whether this item is a queued user message or a queued slash command / model change.
 */
enum QueuePendingItemsKind: string
{
    case Command = 'command';
    case Message = 'message';
}
