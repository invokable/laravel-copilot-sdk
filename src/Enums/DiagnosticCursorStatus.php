<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Cursor status for lifecycle diagnostic reads.
 *
 * @internal
 */
enum DiagnosticCursorStatus: string
{
    case Ok = 'ok';
    case Expired = 'expired';
}
