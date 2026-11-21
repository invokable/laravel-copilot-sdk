<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * @experimental
 */
enum PluginActivationStatus: string
{
    case Activated = 'activated';
    case NotFound = 'notFound';
    case Disabled = 'disabled';
    case NoHooks = 'noHooks';
    case Failed = 'failed';
    case TimedOut = 'timedOut';
}
