<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Attachment type for user message attachments.
 */
enum AttachmentType: string
{
    case DIRECTORY = 'directory';
    case FILE = 'file';
    case GITHUB_REFERENCE = 'github_reference';
    case SELECTION = 'selection';
}
