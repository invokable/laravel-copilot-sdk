<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ManagedPluginProgressPhase: string
{
    case INITIALIZING = 'initializing';
    case INSTALLING = 'installing';
    case UPDATING = 'updating';
    case COMPLETE = 'complete';
}
