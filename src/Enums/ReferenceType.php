<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Reference type for GitHub references in attachments.
 */
enum ReferenceType: string
{
    case DISCUSSION = 'discussion';
    case ISSUE = 'issue';
    case PR = 'pr';
}
