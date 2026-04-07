<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Entry type for directory listings.
 */
enum EntryType: string
{
    case File = 'file';
    case Directory = 'directory';
}
