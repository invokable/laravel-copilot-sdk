<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a session limit prediction result.
 *
 * @experimental This API is experimental and may change or be removed.
 */
enum SessionLimitPredictionResultKind: string
{
    /** Prediction details are available. */
    case AVAILABLE = 'available';

    /** Prediction could not be computed; an explicit reason is provided. */
    case UNAVAILABLE = 'unavailable';
}
