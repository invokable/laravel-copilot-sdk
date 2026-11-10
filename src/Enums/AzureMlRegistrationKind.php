<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Outcome of an Azure ML model registration change.
 *
 * @experimental
 */
enum AzureMlRegistrationKind: string
{
    case Failed = 'failed';
    case Skipped = 'skipped';
    case Written = 'written';
}
