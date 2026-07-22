<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Cumulative resource ceiling that stopped a factory run.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum FactoryRunFailureKind: string
{
    case MAX_TOTAL_SUBAGENTS = 'maxTotalSubagents';
    case TIMEOUT = 'timeout';
}
