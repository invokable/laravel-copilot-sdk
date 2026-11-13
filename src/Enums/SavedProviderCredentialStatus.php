<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum SavedProviderCredentialStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Unavailable = 'unavailable';
}
