<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Runtime state awaited while a host executes a queued command.
 *
 * @internal
 */
enum QueuedCommandWaitRequirement: string
{
    case ManagedPolicyPublished = 'managed-policy-published';
}
