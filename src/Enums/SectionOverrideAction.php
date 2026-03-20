<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Override action for a system prompt section.
 */
enum SectionOverrideAction: string
{
    case REPLACE = 'replace';
    case REMOVE = 'remove';
    case APPEND = 'append';
    case PREPEND = 'prepend';
    case TRANSFORM = 'transform';
}
