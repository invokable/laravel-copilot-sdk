<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Mission Control session sharing status.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum SessionVisibilityStatus: string
{
    /** The session is visible to repository readers. */
    case REPO = 'repo';

    /** The session is restricted to its creator and collaborators. */
    case UNSHARED = 'unshared';
}
