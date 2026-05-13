<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Model capability category for grouping in the model picker.
 */
enum ModelPickerCategory: string
{
    case Lightweight = 'lightweight';
    case Powerful = 'powerful';
    case Versatile = 'versatile';
}
