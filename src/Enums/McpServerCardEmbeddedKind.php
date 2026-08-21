<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for an embedded MCP server card.
 *
 * @experimental
 */
enum McpServerCardEmbeddedKind: string
{
    /** Use the embedded card document. */
    case Embedded = 'embedded';
}
