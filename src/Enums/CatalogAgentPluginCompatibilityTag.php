<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Explicit compatibility declared for an Agent Plugin.
 *
 * @experimental
 */
enum CatalogAgentPluginCompatibilityTag: string
{
    case Canvas = 'canvas';

    case CanvasOnly = 'canvas-only';

    case GitHubCopilot = 'github-copilot';
}
