<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Kind of factory progress line.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum FactoryLogLineKind: string
{
    case LOG = 'log';
    case PHASE = 'phase';
}
