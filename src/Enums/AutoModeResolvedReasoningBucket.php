<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Coarse request-difficulty bucket for UX explainability.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
enum AutoModeResolvedReasoningBucket: string
{
    /** The request looks low-reasoning; a lighter model is appropriate. */
    case Low = 'low';

    /** The request needs a moderate amount of reasoning. */
    case Medium = 'medium';

    /** The request looks high-reasoning; a stronger model is appropriate. */
    case High = 'high';
}
