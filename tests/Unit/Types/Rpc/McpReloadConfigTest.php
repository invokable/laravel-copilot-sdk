<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\McpReloadConfig;
use Revolution\Copilot\Types\Rpc\McpReloadWithConfigRequest;
use Revolution\Copilot\Types\Rpc\McpServerValue;

describe('McpReloadConfig', function () {
    it('preserves configured settings disables separately from ordinary disable lists', function () {
        $config = McpReloadConfig::fromArray([
            'mcpServers' => ['github' => ['type' => 'http', 'url' => 'https://mcp.example.com']],
            'disabledServers' => ['session-disabled'],
            'cliEnabledServers' => ['cli-enabled'],
            'configuredDisabledServers' => ['settings-disabled'],
        ]);

        expect($config->toArray())->toBe([
            'mcpServers' => ['github' => ['type' => 'http', 'url' => 'https://mcp.example.com']],
            'disabledServers' => ['session-disabled'],
            'cliEnabledServers' => ['cli-enabled'],
            'configuredDisabledServers' => ['settings-disabled'],
        ]);
    });

    it('serializes typed MCP server configurations', function () {
        $request = new McpReloadWithConfigRequest(
            config: new McpReloadConfig(
                mcpServers: ['github' => new McpServerValue(type: 'http', url: 'https://mcp.example.com')],
                configuredDisabledServers: ['settings-disabled'],
            ),
        );

        expect($request->toArray())->toBe([
            'config' => [
                'mcpServers' => ['github' => ['type' => 'http', 'url' => 'https://mcp.example.com']],
                'configuredDisabledServers' => ['settings-disabled'],
            ],
        ]);
    });
});
