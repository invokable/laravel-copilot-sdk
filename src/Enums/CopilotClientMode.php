<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum CopilotClientMode: string
{
    case CopilotCli = 'copilot-cli';
    case Empty = 'empty';
}
