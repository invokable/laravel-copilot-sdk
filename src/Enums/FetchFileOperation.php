<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum FetchFileOperation: string
{
    case Create = 'create';
    case Write = 'write';
    case Flush = 'flush';
    case Remove = 'remove';
}
