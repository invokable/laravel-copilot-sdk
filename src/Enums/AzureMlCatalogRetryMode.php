<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Controls how catalog discovery retries a recent sign-in failure.
 *
 * @experimental
 */
enum AzureMlCatalogRetryMode: string
{
    case Background = 'background';
    case Explicit = 'explicit';
}
