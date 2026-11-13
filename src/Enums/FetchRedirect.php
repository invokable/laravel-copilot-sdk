<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum FetchRedirect: string
{
    case Follow = 'follow';
    case SameOrigin = 'sameOrigin';
    case Manual = 'manual';
    case Error = 'error';
}
