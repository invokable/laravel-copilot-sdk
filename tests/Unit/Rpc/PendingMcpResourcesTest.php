<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Types\Rpc\McpResource;
use Revolution\Copilot\Types\Rpc\McpResourceContent;
use Revolution\Copilot\Types\Rpc\McpResourcesListRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesListResult;
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesResult;
use Revolution\Copilot\Types\Rpc\McpResourcesReadRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesReadResult;
use Revolution\Copilot\Types\Rpc\McpResourceTemplate;

describe('PendingMcpResources', function () {
    it('reads MCP resources with typed or array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.resources.read', [
                'serverName' => 'docs',
                'uri' => 'file:///readme.md',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'contents' => [
                    ['uri' => 'file:///readme.md', 'mimeType' => 'text/markdown', 'text' => '# Readme'],
                ],
            ]);

        $resources = (new PendingMcp($client, 'session-abc'))->resources();
        $typedResult = $resources->read(new McpResourcesReadRequest('docs', 'file:///readme.md'));
        $arrayResult = $resources->read(['serverName' => 'docs', 'uri' => 'file:///readme.md']);

        expect($typedResult)->toBeInstanceOf(McpResourcesReadResult::class)
            ->and($typedResult->contents[0])->toBeInstanceOf(McpResourceContent::class)
            ->and($typedResult->contents[0]->text)->toBe('# Readme')
            ->and($arrayResult->contents[0]->text)->toBe('# Readme');
    });

    it('lists MCP resources with pagination for typed or array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.resources.list', [
                'serverName' => 'docs',
                'cursor' => 'page-2',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'resources' => [
                    ['uri' => 'file:///readme.md', 'name' => 'readme', 'mimeType' => 'text/markdown'],
                ],
                'nextCursor' => 'page-3',
            ]);

        $resources = (new PendingMcp($client, 'session-abc'))->resources();
        $typedResult = $resources->list(new McpResourcesListRequest('docs', 'page-2'));
        $arrayResult = $resources->list([
            'serverName' => 'docs',
            'cursor' => 'page-2',
            'sessionId' => 'untrusted-session',
        ]);

        expect($typedResult)->toBeInstanceOf(McpResourcesListResult::class)
            ->and($typedResult->resources[0])->toBeInstanceOf(McpResource::class)
            ->and($typedResult->resources[0]->name)->toBe('readme')
            ->and($typedResult->nextCursor)->toBe('page-3')
            ->and($arrayResult->resources[0]->name)->toBe('readme')
            ->and($arrayResult->nextCursor)->toBe('page-3');
    });

    it('lists MCP resource templates with typed or array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.resources.listTemplates', [
                'serverName' => 'docs',
                'cursor' => 'template-page-2',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'resourceTemplates' => [
                    ['uriTemplate' => 'file:///{path}', 'name' => 'file-by-path'],
                ],
                'nextCursor' => 'template-page-3',
            ]);

        $resources = (new PendingMcp($client, 'session-abc'))->resources();
        $typedResult = $resources->listTemplates(
            new McpResourcesListTemplatesRequest('docs', 'template-page-2'),
        );
        $arrayResult = $resources->listTemplates([
            'serverName' => 'docs',
            'cursor' => 'template-page-2',
        ]);

        expect($typedResult)->toBeInstanceOf(McpResourcesListTemplatesResult::class)
            ->and($typedResult->resourceTemplates[0])->toBeInstanceOf(McpResourceTemplate::class)
            ->and($typedResult->resourceTemplates[0]->uriTemplate)->toBe('file:///{path}')
            ->and($typedResult->nextCursor)->toBe('template-page-3')
            ->and($arrayResult->resourceTemplates[0]->uriTemplate)->toBe('file:///{path}')
            ->and($arrayResult->nextCursor)->toBe('template-page-3');
    });
});
