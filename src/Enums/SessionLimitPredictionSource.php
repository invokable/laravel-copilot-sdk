<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Baseline fallback level used to create the prediction.
 *
 * @experimental This API is experimental and may change or be removed.
 */
enum SessionLimitPredictionSource: string
{
    /** The prediction used the exact resolved model's baseline cell. */
    case MODEL = 'model';

    /** The exact model was unavailable, so the prediction used the model family's baseline cell. */
    case FAMILY = 'family';

    /** No model or family cell was available, so the prediction used the global client-type baseline cell. */
    case GLOBAL = 'global';
}
