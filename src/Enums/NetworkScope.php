<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum NetworkScope: string
{
    case Connection = 'connection';
    case Runtime = 'runtime';
}
