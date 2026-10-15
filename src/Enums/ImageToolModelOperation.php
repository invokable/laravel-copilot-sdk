<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ImageToolModelOperation: string
{
    case Generate = 'generate';
    case Edit = 'edit';
}
