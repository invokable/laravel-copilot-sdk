<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Action the runtime selected for a durable factory pause checkpoint.
 *
 * Whether this execution attempt must pause or may continue.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum FactoryPauseCheckpointAction: string
{
    case CONTINUE = 'continue';
    case PAUSE = 'pause';
}
