<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ModelCallRequestBodyEncoding: string
{
    case IDENTITY = 'identity';
    case GZIP = 'gzip';
    case ZSTD = 'zstd';
}
