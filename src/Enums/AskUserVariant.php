<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Model-facing shape of the built-in ask_user tool.
 */
enum AskUserVariant: string
{
    /** Legacy question-and-answer requests. */
    case LEGACY = 'legacy';

    /** Structured elicitation requests. */
    case ELICITATION = 'elicitation';
}
