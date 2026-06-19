<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Whether the instruction discovery target is a single file or a directory.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum InstructionDiscoveryPathKind: string
{
    case FILE = 'file';
    case DIRECTORY = 'directory';
}
