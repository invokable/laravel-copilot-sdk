<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ShellAuthenticationInputKind: string
{
    case Secret = 'secret';
    case Text = 'text';
    case Confirm = 'confirm';
}
