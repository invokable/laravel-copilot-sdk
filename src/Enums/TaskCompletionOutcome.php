<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Semantic result of evaluating a task completion request.
 */
enum TaskCompletionOutcome: string
{
    case Completed = 'completed';
    case Continue = 'continue';
    case Blocked = 'blocked';
}
