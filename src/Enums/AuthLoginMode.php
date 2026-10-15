<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum AuthLoginMode: string
{
    case Default = 'default';
    case SelectAccount = 'selectAccount';
}
