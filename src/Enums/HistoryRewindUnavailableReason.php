<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Reason a rewind read (rewind points, file-restore preview, or session diff)
 * could not be answered from the session's file-change captures.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum HistoryRewindUnavailableReason: string
{
    case FILE_CHANGE_TRACKING_DISABLED = 'file-change-tracking-disabled';
    case SESSION_BUSY = 'session-busy';
    case UNSUPPORTED_REMOTE_SESSION = 'unsupported-remote-session';
}
