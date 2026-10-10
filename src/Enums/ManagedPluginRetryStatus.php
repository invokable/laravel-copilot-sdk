<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ManagedPluginRetryStatus: string
{
    case INSTALLED = 'installed';
    case UPDATED = 'updated';
    case ALREADY_PRESENT = 'already_present';
    case FAILED = 'failed';
    case DEFERRED = 'deferred';
    case NOT_REQUIRED = 'not_required';
}
