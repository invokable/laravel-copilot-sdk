<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Outcome of a rewind request.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum HistoryRewindOutcome: string
{
    case SUCCESS = 'success';
    case SESSION_BUSY = 'session-busy';
    case FILE_CHANGE_TRACKING_DISABLED = 'file-change-tracking-disabled';
    case UNSUPPORTED_REMOTE_SESSION = 'unsupported-remote-session';
    case FILES_ROLLED_BACK = 'files-rolled-back';
    case ROLLBACK_INCOMPLETE = 'rollback-incomplete';
    case TRUNCATION_FAILED = 'truncation-failed';
    case CHECKPOINT_CLEANUP_FAILED = 'checkpoint-cleanup-failed';
    case SNAPSHOT_PRUNE_FAILED = 'snapshot-prune-failed';
}
