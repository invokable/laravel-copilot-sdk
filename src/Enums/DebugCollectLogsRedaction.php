<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Redaction mode for a debug log bundle entry.
 *
 * @experimental
 */
enum DebugCollectLogsRedaction: string
{
    case PlainText = 'plain-text';
    case EventsJsonl = 'events-jsonl';
    case None = 'none';
}
