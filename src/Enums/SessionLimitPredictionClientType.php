<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Client population used for the prediction baseline.
 *
 * @experimental This API is experimental and may change or be removed.
 */
enum SessionLimitPredictionClientType: string
{
    /** Interactive CLI sessions where a user can accept, edit, or top up the limit. */
    case CLI_INTERACTIVE = 'cli-interactive';

    /** Prompt/non-interactive CLI sessions where the initial limit must cover more of the run. */
    case CLI_PROMPT = 'cli-prompt';
}
