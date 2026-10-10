<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ModelProviderKind: string
{
    case COPILOT = 'copilot';
    case LOKI = 'loki';
}
