<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * User action selected for an exhausted session limit.
 */
enum SessionLimitsExhaustedResponseAction: string
{
    /** Increase the current max by an exact AI Credits amount. */
    case Add = 'add';

    /** Set a new absolute max AI Credits value. */
    case Set = 'set';

    /** Remove the current session limit. */
    case Unset = 'unset';

    /** Leave the limit unchanged and cancel the blocked model request. */
    case Cancel = 'cancel';
}
