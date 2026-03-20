<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Extension discovery source.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum ExtensionSource: string
{
    case PROJECT = 'project';
    case USER = 'user';
}
