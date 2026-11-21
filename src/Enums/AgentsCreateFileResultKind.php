<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum AgentsCreateFileResultKind: string
{
    case Success = 'success';
    case Error = 'error';
}
