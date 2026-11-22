<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingPlugins;
use Revolution\Copilot\Types\Rpc\MarketplaceCheckUpdateResult;
use Revolution\Copilot\Types\Rpc\PluginList;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceCheckUpdateRequest;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchRequest;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchResult;
use Revolution\Copilot\Types\Rpc\SessionPluginDefaults;
use Revolution\Copilot\Types\Rpc\SessionPluginsCheckUpdateRequest;
use Revolution\Copilot\Types\Rpc\SessionPluginsCheckUpdateResult;
use Revolution\Copilot\Types\Rpc\TeamPluginMarketplace;

describe('PendingPlugins', function () {
    it('calls session.plugins.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.list', ['sessionId' => 'session-abc'])
            ->andReturn([
                'plugins' => [
                    [
                        'name' => 'eslint',
                        'marketplace' => 'npm',
                        'enabled' => true,
                        'version' => '8.0.0',
                    ],
                ],
            ]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(PluginList::class)
            ->and($result->plugins)->toHaveCount(1)
            ->and($result->plugins[0]->name)->toBe('eslint');
    });

    it('calls session.plugins.list with empty result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.list', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(PluginList::class)
            ->and($result->plugins)->toBe([]);
    });

    it('gets typed session plugin defaults with only the session id', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.getDefaults', ['sessionId' => 'session-abc'])
            ->andReturn([
                'generation' => 'generation-1',
                'defaultEnabledPlugins' => ['team/plugin@marketplace' => true],
                'teamDefaultEnabledPlugins' => ['team/plugin@marketplace' => false],
                'extraKnownMarketplaces' => [
                    'team-marketplace' => [
                        'source' => [
                            'source' => 'github',
                            'repo' => 'team/plugins',
                        ],
                    ],
                ],
            ]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->getDefaults();

        expect($result)->toBeInstanceOf(SessionPluginDefaults::class)
            ->and($result->generation)->toBe('generation-1')
            ->and($result->defaultEnabledPlugins)->toBe(['team/plugin@marketplace' => true])
            ->and($result->teamDefaultEnabledPlugins)->toBe(['team/plugin@marketplace' => false])
            ->and($result->extraKnownMarketplaces['team-marketplace'])->toBeInstanceOf(TeamPluginMarketplace::class)
            ->and($result->extraKnownMarketplaces['team-marketplace']->source->repo)->toBe('team/plugins');
    });

    it('checks for plugin updates with the typed request and result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.checkUpdate', [
                'name' => 'eslint',
                'marketplace' => 'official',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'updateAvailable' => true,
                'installedVersion' => '1.0.0',
                'latestVersion' => '1.1.0',
            ]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->checkUpdate(new SessionPluginsCheckUpdateRequest('eslint', 'official'));

        expect($result)->toBeInstanceOf(SessionPluginsCheckUpdateResult::class)
            ->and($result->updateAvailable)->toBeTrue()
            ->and($result->installedVersion)->toBe('1.0.0')
            ->and($result->latestVersion)->toBe('1.1.0');
    });

    it('checks for marketplace plugin updates with the typed request and result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.marketplaces.checkUpdate', [
                'name' => 'official',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'updateAvailable' => true,
                'installedVersion' => '1.0.0',
                'latestVersion' => '1.1.0',
                'notFetched' => false,
            ]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->marketplaces()->checkUpdate(new PluginMarketplaceCheckUpdateRequest('official'));

        expect($result)->toBeInstanceOf(MarketplaceCheckUpdateResult::class)
            ->and($result->updateAvailable)->toBeTrue()
            ->and($result->installedVersion)->toBe('1.0.0')
            ->and($result->latestVersion)->toBe('1.1.0')
            ->and($result->notFetched)->toBeFalse();
    });

    it('searches configured marketplaces with typed results and preserves partial errors', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.marketplaces.search', [
                'query' => 'lint',
                'forceRefresh' => false,
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'plugins' => [[
                    'name' => 'eslint',
                    'marketplace' => 'official',
                    'installSpec' => 'eslint@official',
                    'installed' => true,
                    'metadata' => [],
                    'live' => [
                        'enabled' => false,
                        'installedFrom' => 'official',
                    ],
                ]],
                'errors' => [[
                    'marketplace' => 'team',
                    'error' => 'Marketplace unavailable',
                    'scope' => 'workspace',
                ]],
            ]);

        $pending = new PendingPlugins($client, 'session-abc');
        $result = $pending->marketplaces()->search(new PluginMarketplaceSearchRequest('lint', false));

        expect($result)->toBeInstanceOf(PluginMarketplaceSearchResult::class)
            ->and($result->plugins[0]->name)->toBe('eslint')
            ->and($result->plugins[0]->live->enabled)->toBeFalse()
            ->and($result->errors[0]->marketplace)->toBe('team')
            ->and($result->errors[0]->scope)->toBe('workspace');
    });

    it('passes directSourceId values through session plugin enable and disable', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.enable', [
                'names' => ['eslint@official', 'opaque-direct-source-id'],
                'sessionId' => 'session-abc',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.plugins.disable', [
                'names' => ['eslint@official', 'opaque-direct-source-id'],
                'sessionId' => 'session-abc',
            ]);

        $pending = new PendingPlugins($client, 'session-abc');
        $pending->enable(['names' => ['eslint@official', 'opaque-direct-source-id']]);
        $pending->disable(['names' => ['eslint@official', 'opaque-direct-source-id']]);
    });
});
