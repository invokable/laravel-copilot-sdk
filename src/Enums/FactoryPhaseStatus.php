<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Derived lifecycle state of a factory phase.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum FactoryPhaseStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case SKIPPED = 'skipped';
}
