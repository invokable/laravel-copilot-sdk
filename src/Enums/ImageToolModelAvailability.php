<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ImageToolModelAvailability: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
}
