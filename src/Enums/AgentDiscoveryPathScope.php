<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Which tier this agent discovery directory belongs to.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum AgentDiscoveryPathScope: string
{
    case User = 'user';
    case Project = 'project';
}
