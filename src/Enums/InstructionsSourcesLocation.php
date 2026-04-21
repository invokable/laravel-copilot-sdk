<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Where the instruction source lives — used for UI grouping.
 */
enum InstructionsSourcesLocation: string
{
    case USER = 'user';
    case REPOSITORY = 'repository';
    case WORKING_DIRECTORY = 'working-directory';
}
