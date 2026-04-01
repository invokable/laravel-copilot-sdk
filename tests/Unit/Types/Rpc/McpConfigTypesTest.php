<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\McpConfigAddParams;
use Revolution\Copilot\Types\Rpc\McpConfigListResult;
use Revolution\Copilot\Types\Rpc\McpConfigRemoveParams;
use Revolution\Copilot\Types\Rpc\McpConfigUpdateParams;
use Revolution\Copilot\Types\Rpc\McpServerValue;

describe('McpServerValue', function () {
    it('can be created from array with all fields', function () {
        $data = McpServerValue::fromArray([
            'type' => 'local',
            'command' => 'php',
            'args' => ['artisan', 'boost:mcp'],
            'cwd' => '/app',
            'env' => ['APP_ENV' => 'testing'],
            'tools' => ['*'],
            'isDefaultServer' => false,
            'timeout' => 30000,
        ]);

        expect($data->type)->toBe('local')
            ->and($data->command)->toBe('php')
            ->and($data->args)->toBe(['artisan', 'boost:mcp'])
            ->and($data->cwd)->toBe('/app')
            ->and($data->env)->toBe(['APP_ENV' => 'testing'])
            ->and($data->tools)->toBe(['*'])
            ->and($data->isDefaultServer)->toBeFalse()
            ->and($data->timeout)->toBe(30000);
    });

    it('can be created for http server', function () {
        $data = McpServerValue::fromArray([
            'type' => 'http',
            'url' => 'https://mcp.example.com',
            'headers' => ['Authorization' => 'Bearer token'],
            'oauthClientId' => 'client-123',
            'oauthPublicClient' => true,
        ]);

        expect($data->type)->toBe('http')
            ->and($data->url)->toBe('https://mcp.example.com')
            ->and($data->headers)->toBe(['Authorization' => 'Bearer token'])
            ->and($data->oauthClientId)->toBe('client-123')
            ->and($data->oauthPublicClient)->toBeTrue();
    });

    it('handles default values', function () {
        $data = McpServerValue::fromArray([]);

        expect($data->type)->toBeNull()
            ->and($data->command)->toBeNull()
            ->and($data->args)->toBeNull()
            ->and($data->url)->toBeNull();
    });

    it('converts to array', function () {
        $data = McpServerValue::fromArray([
            'type' => 'local',
            'command' => 'node',
            'args' => ['server.js'],
        ]);

        expect($data->toArray())
            ->toHaveKey('type', 'local')
            ->toHaveKey('command', 'node')
            ->toHaveKey('args', ['server.js'])
            ->not->toHaveKey('url');
    });
});

describe('McpConfigListResult', function () {
    it('can be created from array', function () {
        $result = McpConfigListResult::fromArray([
            'servers' => [
                'github' => [
                    'type' => 'local',
                    'command' => 'gh',
                    'args' => ['mcp'],
                ],
                'remote' => [
                    'type' => 'http',
                    'url' => 'https://mcp.example.com',
                ],
            ],
        ]);

        expect($result->servers)->toHaveCount(2)
            ->and($result->servers['github'])->toBeInstanceOf(McpServerValue::class)
            ->and($result->servers['github']->command)->toBe('gh')
            ->and($result->servers['remote']->url)->toBe('https://mcp.example.com');
    });

    it('handles empty servers', function () {
        $result = McpConfigListResult::fromArray([]);

        expect($result->servers)->toBe([]);
    });

    it('converts to array', function () {
        $result = McpConfigListResult::fromArray([
            'servers' => [
                'test' => ['type' => 'local', 'command' => 'test'],
            ],
        ]);

        $array = $result->toArray();
        expect($array)->toHaveKey('servers')
            ->and($array['servers']['test'])->toHaveKey('type', 'local');
    });
});

describe('McpConfigAddParams', function () {
    it('can be created with typed config', function () {
        $params = new McpConfigAddParams(
            name: 'my-server',
            config: new McpServerValue(
                type: 'local',
                command: 'php',
                args: ['artisan', 'mcp'],
            ),
        );

        expect($params->name)->toBe('my-server')
            ->and($params->config)->toBeInstanceOf(McpServerValue::class);
    });

    it('can be created from array', function () {
        $params = McpConfigAddParams::fromArray([
            'name' => 'my-server',
            'config' => ['type' => 'http', 'url' => 'https://example.com'],
        ]);

        expect($params->name)->toBe('my-server')
            ->and($params->config)->toBeInstanceOf(McpServerValue::class)
            ->and($params->config->url)->toBe('https://example.com');
    });

    it('converts to array', function () {
        $params = new McpConfigAddParams(
            name: 'test',
            config: new McpServerValue(type: 'local', command: 'node'),
        );

        $array = $params->toArray();
        expect($array)->toHaveKey('name', 'test')
            ->and($array['config'])->toHaveKey('type', 'local');
    });
});

describe('McpConfigUpdateParams', function () {
    it('can be created with typed config', function () {
        $params = new McpConfigUpdateParams(
            name: 'my-server',
            config: new McpServerValue(type: 'http', url: 'https://new-url.com'),
        );

        expect($params->name)->toBe('my-server')
            ->and($params->config)->toBeInstanceOf(McpServerValue::class);
    });

    it('can be created from array', function () {
        $params = McpConfigUpdateParams::fromArray([
            'name' => 'my-server',
            'config' => ['type' => 'local', 'command' => 'php'],
        ]);

        expect($params->name)->toBe('my-server')
            ->and($params->config)->toBeInstanceOf(McpServerValue::class);
    });

    it('converts to array', function () {
        $params = new McpConfigUpdateParams(
            name: 'test',
            config: new McpServerValue(type: 'sse', url: 'https://sse.example.com'),
        );

        $array = $params->toArray();
        expect($array)->toHaveKey('name', 'test')
            ->and($array['config'])->toHaveKey('type', 'sse');
    });
});

describe('McpConfigRemoveParams', function () {
    it('can be created', function () {
        $params = new McpConfigRemoveParams(name: 'my-server');

        expect($params->name)->toBe('my-server');
    });

    it('can be created from array', function () {
        $params = McpConfigRemoveParams::fromArray(['name' => 'old-server']);

        expect($params->name)->toBe('old-server');
    });

    it('converts to array', function () {
        $params = new McpConfigRemoveParams(name: 'test');

        expect($params->toArray())->toBe(['name' => 'test']);
    });
});
