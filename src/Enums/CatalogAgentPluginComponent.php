<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * A kind of component an Agent Plugin contributes.
 *
 * @experimental
 */
enum CatalogAgentPluginComponent: string
{
    case Agent = 'agent';

    case Command = 'command';

    case Skill = 'skill';

    case Hook = 'hook';

    case McpServer = 'mcp-server';

    case LspServer = 'lsp-server';

    case Extension = 'extension';

    case Canvas = 'canvas';
}
