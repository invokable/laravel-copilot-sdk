<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Where the instruction source lives — used for UI grouping.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum InstructionSourceLocation: string
{
    case USER = 'user';
    case REPOSITORY = 'repository';
    case WORKING_DIRECTORY = 'working-directory';
    case PLUGIN = 'plugin';
}
