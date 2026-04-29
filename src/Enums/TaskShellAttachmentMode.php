<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Whether the shell runs inside a managed PTY session or as an independent background process.
 */
enum TaskShellAttachmentMode: string
{
    case Attached = 'attached';
    case Detached = 'detached';
}
