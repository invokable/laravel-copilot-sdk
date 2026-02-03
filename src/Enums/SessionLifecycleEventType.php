<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Types of session lifecycle events.
 *
 * These events are emitted when sessions are created, deleted, updated,
 * or change foreground/background state (in TUI+server mode).
 */
enum SessionLifecycleEventType: string
{
    case SESSION_CREATED = 'session.created';
    case SESSION_DELETED = 'session.deleted';
    case SESSION_UPDATED = 'session.updated';
    case SESSION_FOREGROUND = 'session.foreground';
    case SESSION_BACKGROUND = 'session.background';
}
