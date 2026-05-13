<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Relative cost tier for token-based billing users.
 */
enum ModelPickerPriceCategory: string
{
    case High = 'high';
    case Low = 'low';
    case Medium = 'medium';
    case VeryHigh = 'very_high';
}
