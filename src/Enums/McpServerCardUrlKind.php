<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a URL-backed MCP server card.
 *
 * @experimental
 */
enum McpServerCardUrlKind: string
{
    /** Retrieve the card from its URL. */
    case Url = 'url';
}
