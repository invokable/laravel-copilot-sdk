<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum AuthLoginMethod: string
{
    case WEB = 'web';
    case DEVICE_CODE = 'device-code';
}
