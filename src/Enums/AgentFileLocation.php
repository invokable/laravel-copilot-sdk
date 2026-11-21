<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum AgentFileLocation: string
{
    case Project = 'project';
    case User = 'user';
}
