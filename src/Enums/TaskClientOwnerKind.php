<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum TaskClientOwnerKind: string
{
    case Extension = 'extension';
    case Sdk = 'sdk';
}
