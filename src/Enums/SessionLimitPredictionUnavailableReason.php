<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Reason a prediction could not be computed.
 *
 * @experimental This API is experimental and may change or be removed.
 */
enum SessionLimitPredictionUnavailableReason: string
{
    /** The current model is auto and has not resolved to a concrete model yet. */
    case AUTO_UNRESOLVED = 'auto_unresolved';

    /** No model was provided and the session does not currently have a selected model. */
    case NO_MODEL = 'no_model';
}
