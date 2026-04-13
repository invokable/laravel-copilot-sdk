<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ServerSource;
use Revolution\Copilot\Types\Rpc\DiscoveredMcpServer;
use Revolution\Copilot\Types\Rpc\McpDiscoverParams;
use Revolution\Copilot\Types\Rpc\McpDiscoverResult;

describe('McpDiscoverParams', function () {
    it('can be created with working directory', function () {
        $params = new McpDiscoverParams(workingDirectory: '/workspace/my-project');

        expect($params->workingDirectory)->toBe('/workspace/my-project');
    });

    it('can be created with default values', function () {
        $params = new McpDiscoverParams;

        expect($params->workingDirectory)->toBeNull();
    });

    it('can be created from array', function () {
        $params = McpDiscoverParams::fromArray([
            'workingDirectory' => '/workspace',
        ]);

        expect($params->workingDirectory)->toBe('/workspace');
    });

    it('can be created from empty array', function () {
        $params = McpDiscoverParams::fromArray([]);

        expect($params->workingDirectory)->toBeNull();
    });

    it('converts to array without null values', function () {
        $params = new McpDiscoverParams;

        expect($params->toArray())->toBe([]);
    });

    it('converts to array with working directory', function () {
        $params = new McpDiscoverParams(workingDirectory: '/workspace');

        expect($params->toArray())->toBe(['workingDirectory' => '/workspace']);
    });

    it('implements Arrayable interface', function () {
        expect(new McpDiscoverParams)->toBeInstanceOf(Arrayable::class);
    });
});

describe('DiscoveredMcpServer', function () {
    it('can be created from array with all fields', function () {
        $server = DiscoveredMcpServer::fromArray([
            'name' => 'github',
            'type' => 'local',
            'source' => 'user',
            'enabled' => true,
        ]);

        expect($server->name)->toBe('github')
            ->and($server->type)->toBe('local')
            ->and($server->source)->toBe(ServerSource::USER)
            ->and($server->enabled)->toBeTrue();
    });

    it('can be created from array without optional type', function () {
        $server = DiscoveredMcpServer::fromArray([
            'name' => 'test',
            'source' => 'workspace',
            'enabled' => false,
        ]);

        expect($server->type)->toBeNull()
            ->and($server->source)->toBe(ServerSource::WORKSPACE);
    });

    it('converts to array', function () {
        $server = new DiscoveredMcpServer(
            name: 'github',
            source: ServerSource::BUILTIN,
            enabled: true,
            type: 'stdio',
        );

        expect($server->toArray())->toBe([
            'name' => 'github',
            'source' => 'builtin',
            'enabled' => true,
            'type' => 'stdio',
        ]);
    });

    it('excludes null type from toArray', function () {
        $server = new DiscoveredMcpServer(
            name: 'test',
            source: ServerSource::PLUGIN,
            enabled: false,
        );

        expect($server->toArray())->not->toHaveKey('type');
    });

    it('implements Arrayable interface', function () {
        $server = new DiscoveredMcpServer(name: 'x', source: ServerSource::USER, enabled: true);
        expect($server)->toBeInstanceOf(Arrayable::class);
    });
});

describe('McpDiscoverResult', function () {
    it('can be created from array with servers', function () {
        $result = McpDiscoverResult::fromArray([
            'servers' => [
                [
                    'name' => 'github',
                    'type' => 'local',
                    'source' => 'user',
                    'enabled' => true,
                ],
                [
                    'name' => 'custom',
                    'source' => 'workspace',
                    'enabled' => false,
                ],
            ],
        ]);

        expect($result->servers)->toHaveCount(2)
            ->and($result->servers[0])->toBeInstanceOf(DiscoveredMcpServer::class)
            ->and($result->servers[0]->name)->toBe('github')
            ->and($result->servers[1]->name)->toBe('custom')
            ->and($result->servers[1]->enabled)->toBeFalse();
    });

    it('can be created from array with empty servers', function () {
        $result = McpDiscoverResult::fromArray(['servers' => []]);

        expect($result->servers)->toBe([]);
    });

    it('can be created from array without servers key', function () {
        $result = McpDiscoverResult::fromArray([]);

        expect($result->servers)->toBe([]);
    });

    it('converts to array', function () {
        $result = new McpDiscoverResult(servers: [
            new DiscoveredMcpServer(name: 'test', source: ServerSource::BUILTIN, enabled: true, type: 'http'),
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('servers')
            ->and($array['servers'])->toHaveCount(1)
            ->and($array['servers'][0]['name'])->toBe('test');
    });

    it('implements Arrayable interface', function () {
        expect(new McpDiscoverResult(servers: []))->toBeInstanceOf(Arrayable::class);
    });
});
