<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum MemoryImportStatus: string
{
    case Imported = 'imported';
    case Conflict = 'conflict';
    case Disabled = 'disabled';
    case Unavailable = 'unavailable';
    case Failed = 'failed';
}
