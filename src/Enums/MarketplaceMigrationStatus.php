<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * @experimental
 */
enum MarketplaceMigrationStatus: string
{
    case AlreadyRan = 'alreadyRan';
    case NotApplicable = 'notApplicable';
    case AlreadyRegistered = 'alreadyRegistered';
    case BlockedByManagedPolicy = 'blockedByManagedPolicy';
    case Migrated = 'migrated';
}
