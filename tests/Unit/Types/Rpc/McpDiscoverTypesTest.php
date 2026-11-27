<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpTransportType;
use Revolution\Copilot\Enums\ServerSource;
use Revolution\Copilot\Types\Rpc\DiscoveredMcpServer;
use Revolution\Copilot\Types\Rpc\InstalledPlugin;
use Revolution\Copilot\Types\Rpc\McpDiscoverPluginServersRequest;
use Revolution\Copilot\Types\Rpc\McpDiscoverPluginServersResult;
use Revolution\Copilot\Types\Rpc\McpDiscoverRequest;
use Revolution\Copilot\Types\Rpc\McpDiscoverResult;

describe('McpDiscoverPluginServersRequest', function () {
    it('round trips installed plugins and optional settings', function () {
        $data = [
            'installedPlugins' => [[
                'name' => 'example',
                'marketplace' => 'community',
                'installed_at' => '2026-01-01',
                'enabled' => false,
                'cache_path' => '/plugins/example',
            ]],
            'builtinPluginNames' => [],
            'configDir' => '/config',
            'homeDirectory' => '/home/user',
        ];

        $request = McpDiscoverPluginServersRequest::fromArray($data);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->installedPlugins[0])->toBeInstanceOf(InstalledPlugin::class)
            ->and($request->toArray())->toBe($data);
    });

    it('accepts typed and array plugins and keeps an empty required list', function () {
        $plugin = new InstalledPlugin(name: 'example', marketplace: 'community', installedAt: '2026-01-01', enabled: true);

        expect((new McpDiscoverPluginServersRequest(installedPlugins: []))->toArray())->toBe(['installedPlugins' => []])
            ->and((new McpDiscoverPluginServersRequest(installedPlugins: [$plugin, $plugin->toArray()]))->toArray())
            ->toBe(['installedPlugins' => [$plugin->toArray(), $plugin->toArray()]]);
    });

    it('rejects a missing or invalid installed plugins field', function (array $data) {
        McpDiscoverPluginServersRequest::fromArray($data);
    })->with([[[]], [['installedPlugins' => null]], [['installedPlugins' => 'invalid']]])->throws(InvalidArgumentException::class);
});

describe('McpDiscoverPluginServersResult', function () {
    it('round trips server configurations and optional server lists', function () {
        $data = [
            'mcpServers' => [
                'local' => ['type' => 'stdio', 'command' => 'example', 'args' => [], 'extra' => ['key' => 'value']],
                'remote' => ['type' => 'http', 'url' => 'https://example.com'],
            ],
            'disabledServers' => [],
            'enabledServers' => ['local'],
        ];

        $result = McpDiscoverPluginServersResult::fromArray($data);

        expect($result)->toBeInstanceOf(Arrayable::class)
            ->and($result->toArray())->toBe($data);
    });

    it('omits absent optional server lists', function () {
        expect(McpDiscoverPluginServersResult::fromArray(['mcpServers' => []])->toArray())
            ->toBe(['mcpServers' => []]);
    });
});

describe('McpDiscoverRequest', function () {
    it('can be created with working directory', function () {
        $params = new McpDiscoverRequest(workingDirectory: '/workspace/my-project');

        expect($params->workingDirectory)->toBe('/workspace/my-project');
    });

    it('can be created with default values', function () {
        $params = new McpDiscoverRequest;

        expect($params->workingDirectory)->toBeNull();
    });

    it('can be created from array', function () {
        $params = McpDiscoverRequest::fromArray([
            'workingDirectory' => '/workspace',
        ]);

        expect($params->workingDirectory)->toBe('/workspace');
    });

    it('can be created from empty array', function () {
        $params = McpDiscoverRequest::fromArray([]);

        expect($params->workingDirectory)->toBeNull();
    });

    it('converts to array without null values', function () {
        $params = new McpDiscoverRequest;

        expect($params->toArray())->toBe([]);
    });

    it('converts to array with working directory', function () {
        $params = new McpDiscoverRequest(workingDirectory: '/workspace');

        expect($params->toArray())->toBe(['workingDirectory' => '/workspace']);
    });

    it('implements Arrayable interface', function () {
        expect(new McpDiscoverRequest)->toBeInstanceOf(Arrayable::class);
    });
});

describe('DiscoveredMcpServer', function () {
    it('can be created from array with known transport type', function () {
        $server = DiscoveredMcpServer::fromArray([
            'name' => 'github',
            'type' => 'stdio',
            'source' => 'user',
            'enabled' => true,
        ]);

        expect($server->name)->toBe('github')
            ->and($server->type)->toBe(McpTransportType::STDIO)
            ->and($server->source)->toBe(ServerSource::USER)
            ->and($server->enabled)->toBeTrue();
    });

    it('can be created from array with unknown transport type as fallback string', function () {
        $server = DiscoveredMcpServer::fromArray([
            'name' => 'custom',
            'type' => 'custom-transport',
            'source' => 'user',
            'enabled' => true,
        ]);

        expect($server->type)->toBe('custom-transport');
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

    it('converts to array with enum type', function () {
        $server = new DiscoveredMcpServer(
            name: 'github',
            source: ServerSource::BUILTIN,
            enabled: true,
            type: McpTransportType::STDIO,
        );

        expect($server->toArray())->toBe([
            'name' => 'github',
            'source' => 'builtin',
            'enabled' => true,
            'type' => 'stdio',
        ]);
    });

    it('converts to array with string type', function () {
        $server = new DiscoveredMcpServer(
            name: 'github',
            source: ServerSource::BUILTIN,
            enabled: true,
            type: 'custom',
        );

        expect($server->toArray())->toBe([
            'name' => 'github',
            'source' => 'builtin',
            'enabled' => true,
            'type' => 'custom',
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

    it('resolves all known transport types', function () {
        foreach (['stdio', 'http', 'sse', 'memory'] as $type) {
            $server = DiscoveredMcpServer::fromArray([
                'name' => 'test',
                'type' => $type,
                'source' => 'user',
                'enabled' => true,
            ]);
            expect($server->type)->toBeInstanceOf(McpTransportType::class);
        }
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
            new DiscoveredMcpServer(name: 'test', source: ServerSource::BUILTIN, enabled: true, type: McpTransportType::HTTP),
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
