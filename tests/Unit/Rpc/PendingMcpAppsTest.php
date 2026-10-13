<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\MCPAppsDisplayMode;
use Revolution\Copilot\Enums\MCPAppsHostContextDetailsPlatform;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingMcpApps;
use Revolution\Copilot\Types\Rpc\McpAppsCallToolRequest;
use Revolution\Copilot\Types\Rpc\MCPAppsDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\McpAppsDiagnoseResult;
use Revolution\Copilot\Types\Rpc\McpAppsHostContext;
use Revolution\Copilot\Types\Rpc\MCPAppsListToolsRequest;
use Revolution\Copilot\Types\Rpc\McpAppsListToolsResult;
use Revolution\Copilot\Types\Rpc\McpAppsReadResourceRequest;
use Revolution\Copilot\Types\Rpc\McpAppsReadResourceResult;
use Revolution\Copilot\Types\Rpc\McpAppsResourceContent;
use Revolution\Copilot\Types\Rpc\McpAppsSetHostContextDetails;
use Revolution\Copilot\Types\Rpc\McpAppsSetHostContextRequest;

describe('PendingMcpApps', function () {
    it('is available from PendingMcp', function () {
        $client = Mockery::mock(JsonRpcClient::class);

        expect((new PendingMcp($client, 'session-abc'))->apps())
            ->toBeInstanceOf(PendingMcpApps::class);
    });

    it('calls session.mcp.apps.listTools and returns typed tools', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.apps.listTools', [
                'originServerName' => 'origin-server',
                'serverName' => 'app-server',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'tools' => [
                    ['name' => 'lookup', 'description' => 'Look up a value'],
                ],
            ]);

        $result = (new PendingMcp($client, 'session-abc'))->apps()->listTools(
            new MCPAppsListToolsRequest(originServerName: 'origin-server', serverName: 'app-server'),
        );

        expect($result)->toBeInstanceOf(McpAppsListToolsResult::class)
            ->and($result->tools)->toBe([['name' => 'lookup', 'description' => 'Look up a value']]);
    });

    it('calls session.mcp.apps.callTool with arguments and returns the raw result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.apps.callTool', [
                'serverName' => 'app-server',
                'toolName' => 'lookup',
                'arguments' => ['query' => 'hello'],
                'originServerName' => 'origin-server',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['content' => [['type' => 'text', 'text' => 'found']]]);

        $result = (new PendingMcp($client, 'session-abc'))->apps()->callTool(
            new McpAppsCallToolRequest(
                serverName: 'app-server',
                toolName: 'lookup',
                originServerName: 'origin-server',
                arguments: ['query' => 'hello'],
            ),
        );

        expect($result)->toBe(['content' => [['type' => 'text', 'text' => 'found']]]);
    });

    it('omits callTool arguments when they are not provided', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.apps.callTool', [
                'serverName' => 'app-server',
                'toolName' => 'lookup',
                'originServerName' => 'origin-server',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([]);

        $result = (new PendingMcp($client, 'session-abc'))->apps()->callTool([
            'serverName' => 'app-server',
            'toolName' => 'lookup',
            'originServerName' => 'origin-server',
        ]);

        expect($result)->toBe([]);
    });

    it('calls session.mcp.apps.readResource and returns typed contents', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.apps.readResource', [
                'serverName' => 'app-server',
                'uri' => 'ui://app/index.html',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'contents' => [
                    ['uri' => 'ui://app/index.html', 'mimeType' => 'text/html', 'text' => '<main>App</main>'],
                ],
            ]);

        $result = (new PendingMcp($client, 'session-abc'))->apps()->readResource(
            new McpAppsReadResourceRequest(serverName: 'app-server', uri: 'ui://app/index.html'),
        );

        expect($result)->toBeInstanceOf(McpAppsReadResourceResult::class)
            ->and($result->contents[0])->toBeInstanceOf(McpAppsResourceContent::class)
            ->and($result->contents[0]->text)->toBe('<main>App</main>');
    });

    it('calls session.mcp.apps.getHostContext and returns the context', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.apps.getHostContext', ['sessionId' => 'session-abc'])
            ->andReturn([
                'context' => [
                    'theme' => 'dark',
                    'displayMode' => 'inline',
                    'availableDisplayModes' => ['inline', 'fullscreen'],
                    'platform' => 'web',
                ],
            ]);

        $result = (new PendingMcp($client, 'session-abc'))->apps()->getHostContext();

        expect($result)->toBeInstanceOf(McpAppsHostContext::class)
            ->and($result->context->theme)->toBe('dark')
            ->and($result->context->displayMode)->toBe(MCPAppsDisplayMode::INLINE)
            ->and($result->context->availableDisplayModes)->toBe([
                MCPAppsDisplayMode::INLINE,
                MCPAppsDisplayMode::FULLSCREEN,
            ])
            ->and($result->context->platform)->toBe(MCPAppsHostContextDetailsPlatform::WEB);
    });

    it('calls session.mcp.apps.setHostContext and returns no result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.apps.setHostContext', [
                'context' => [
                    'theme' => 'dark',
                    'locale' => 'ja-JP',
                    'customContext' => ['enabled' => true],
                ],
                'sessionId' => 'session-abc',
            ])
            ->andReturnNull();

        $request = new McpAppsSetHostContextRequest(
            context: new McpAppsSetHostContextDetails(
                theme: 'dark',
                locale: 'ja-JP',
                additionalProperties: ['customContext' => ['enabled' => true]],
            ),
        );

        expect((new PendingMcp($client, 'session-abc'))->apps()->setHostContext($request))->toBeNull();
    });

    it('calls session.mcp.apps.diagnose and returns typed diagnostics', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.apps.diagnose', [
                'serverName' => 'app-server',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'capability' => [
                    'advertised' => true,
                    'featureFlagEnabled' => true,
                    'sessionHasMcpApps' => true,
                ],
                'server' => [
                    'connected' => true,
                    'sampleToolNames' => ['lookup'],
                    'toolCount' => 1.0,
                    'toolsWithUiMeta' => 1.0,
                ],
            ]);

        $result = (new PendingMcp($client, 'session-abc'))->apps()->diagnose(
            new MCPAppsDiagnoseRequest(serverName: 'app-server'),
        );

        expect($result)->toBeInstanceOf(McpAppsDiagnoseResult::class)
            ->and($result->capability->sessionHasMcpApps)->toBeTrue()
            ->and($result->server->sampleToolNames)->toBe(['lookup']);
    });
});

describe('MCP Apps request types', function () {
    it('round-trips callTool arguments when omitted or specified', function () {
        $withoutArguments = McpAppsCallToolRequest::fromArray([
            'serverName' => 'app-server',
            'toolName' => 'lookup',
            'originServerName' => 'origin-server',
        ]);
        $withArguments = McpAppsCallToolRequest::fromArray([
            'serverName' => 'app-server',
            'toolName' => 'lookup',
            'arguments' => ['query' => 'hello'],
            'originServerName' => 'origin-server',
        ]);

        expect($withoutArguments->toArray())->toBe([
            'serverName' => 'app-server',
            'toolName' => 'lookup',
            'originServerName' => 'origin-server',
        ])->and($withArguments->toArray())->toBe([
            'serverName' => 'app-server',
            'toolName' => 'lookup',
            'arguments' => ['query' => 'hello'],
            'originServerName' => 'origin-server',
        ]);
    });

    it('preserves additional setHostContext fields through array conversion', function () {
        $context = [
            'theme' => 'dark',
            'locale' => 'ja-JP',
            'customContext' => ['enabled' => true],
        ];
        $request = McpAppsSetHostContextRequest::fromArray(['context' => $context]);

        expect($request->context->toArray())->toBe($context)
            ->and($request->toArray())->toBe(['context' => $context]);
    });
});
