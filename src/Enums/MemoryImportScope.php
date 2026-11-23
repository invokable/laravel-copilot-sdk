<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum MemoryImportScope: string
{
    case User = 'user';
    case Repository = 'repository';
}
