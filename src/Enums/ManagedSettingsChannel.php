<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** A source channel accepted by managed-settings composition. */
enum ManagedSettingsChannel: string
{
    case DEVICE = 'device';
    case SERVER = 'server';
    case POLICY_HELPER = 'policyHelper';
}
