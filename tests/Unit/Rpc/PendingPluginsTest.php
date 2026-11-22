<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingPlugins;
use Revolution\Copilot\Types\Rpc\PluginList;
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
