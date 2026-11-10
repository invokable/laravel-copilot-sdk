<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Managed Azure ML registration change to perform.
 *
 * @experimental
 */
enum AzureMlRegistrationOperation: string
{
    case Remove = 'remove';
    case Restore = 'restore';
    case Upsert = 'upsert';
}
