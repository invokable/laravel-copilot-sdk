<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerPlugins;
use Revolution\Copilot\Rpc\PendingServerPluginsBuiltin;
use Revolution\Copilot\Rpc\PendingServerPluginsMarketplaces;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\Rpc\MarketplaceAddResult;
use Revolution\Copilot\Types\Rpc\MarketplaceListResult;
use Revolution\Copilot\Types\Rpc\MarketplaceRefreshResult;
use Revolution\Copilot\Types\Rpc\MarketplaceRemoveResult;
use Revolution\Copilot\Types\Rpc\PluginInstallResult;
use Revolution\Copilot\Types\Rpc\PluginList;
use Revolution\Copilot\Types\Rpc\PluginsBuiltinSetRequest;
use Revolution\Copilot\Types\Rpc\PluginsDisableRequest;
use Revolution\Copilot\Types\Rpc\PluginsEnableRequest;
use Revolution\Copilot\Types\Rpc\PluginsInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesAddRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesBrowseRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesRefreshRequest;
use Revolution\Copilot\Types\Rpc\PluginsMarketplacesRemoveRequest;
use Revolution\Copilot\Types\Rpc\PluginsUninstallRequest;
use Revolution\Copilot\Types\Rpc\PluginsUpdateRequest;
use Revolution\Copilot\Types\Rpc\PluginUpdateAllResult;
use Revolution\Copilot\Types\Rpc\PluginUpdateResult;

describe('PendingServerPlugins', function () {
    it('provides built-in and marketplace operations', function () {
        $client = new JsonRpcClient(new StdioTransport(
            fopen('php://memory', 'r'),
            fopen('php://memory', 'w'),
        ));
        $plugins = new PendingServerPlugins($client);

        expect($plugins->builtin())->toBeInstanceOf(PendingServerPluginsBuiltin::class)
            ->and($plugins->marketplaces())->toBeInstanceOf(PendingServerPluginsMarketplaces::class);
    });

    it('lists plugins without a session ID', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.list', [])
            ->andReturn([
                'plugins' => [[
                    'name' => 'eslint',
                    'marketplace' => 'official',
                    'enabled' => true,
                    'version' => '1.2.3',
                ]],
            ]);

        $result = (new PendingServerPlugins($client))->list();

        expect($result)->toBeInstanceOf(PluginList::class)
            ->and($result->plugins[0]->name)->toBe('eslint')
            ->and($result->plugins[0]->version)->toBe('1.2.3');
    });

    it('installs a plugin using the typed source request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.install', [
                'source' => 'eslint@official',
                'workingDirectory' => '/workspace',
            ])
            ->andReturn([
                'plugin' => [
                    'name' => 'eslint',
                    'marketplace' => 'official',
                    'enabled' => true,
                    'version' => '1.2.3',
                ],
                'skillsInstalled' => 2,
                'postInstallMessage' => 'Restart to activate.',
            ]);

        $result = (new PendingServerPlugins($client))->install(
            new PluginsInstallRequest('eslint@official', '/workspace'),
        );

        expect($result)->toBeInstanceOf(PluginInstallResult::class)
            ->and($result->plugin->name)->toBe('eslint')
            ->and($result->skillsInstalled)->toBe(2)
            ->and($result->postInstallMessage)->toBe('Restart to activate.');
    });

    it('uninstalls a plugin with a request and no session ID', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.uninstall', [
                'name' => 'eslint@official',
                'directSourceId' => 'github:owner/repo',
            ])
            ->andReturn([]);

        (new PendingServerPlugins($client))->uninstall(
            new PluginsUninstallRequest('eslint@official', 'github:owner/repo'),
        );
    });

    it('updates a plugin and decodes its version metadata', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.update', ['name' => 'eslint@official'])
            ->andReturn([
                'skillsInstalled' => 1,
                'newVersion' => '2.0.0',
                'previousVersion' => '1.2.3',
            ]);

        $result = (new PendingServerPlugins($client))->update(new PluginsUpdateRequest('eslint@official'));

        expect($result)->toBeInstanceOf(PluginUpdateResult::class)
            ->and($result->newVersion)->toBe('2.0.0')
            ->and($result->previousVersion)->toBe('1.2.3')
            ->and($result->skillsInstalled)->toBe(1);
    });

    it('updates all plugins without request parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.updateAll', [])
            ->andReturn([
                'results' => [[
                    'marketplace' => 'official',
                    'name' => 'eslint',
                    'success' => true,
                    'skillsInstalled' => 1,
                ]],
            ]);

        $result = (new PendingServerPlugins($client))->updateAll();

        expect($result)->toBeInstanceOf(PluginUpdateAllResult::class)
            ->and($result->results[0]->name)->toBe('eslint')
            ->and($result->results[0]->success)->toBeTrue()
            ->and($result->results[0]->skillsInstalled)->toBe(1);
    });

    it('enables plugins with the supplied names and working directory', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.enable', [
                'names' => ['eslint@official'],
                'workingDirectory' => '/workspace',
            ])
            ->andReturn([]);

        (new PendingServerPlugins($client))->enable([
            'names' => ['eslint@official'],
            'workingDirectory' => '/workspace',
        ]);
    });

    it('enables plugins from a typed request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.enable', [
                'names' => ['eslint@official'],
                'workingDirectory' => '/workspace',
            ])
            ->andReturn([]);

        (new PendingServerPlugins($client))->enable(
            new PluginsEnableRequest(['eslint@official'], '/workspace'),
        );
    });

    it('disables plugins through a request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.disable', [
                'names' => ['eslint@official'],
                'workingDirectory' => '/workspace',
            ])
            ->andReturn([]);

        (new PendingServerPlugins($client))->disable(
            new PluginsDisableRequest(['eslint@official'], '/workspace'),
        );
    });
});

describe('PendingServerPluginsBuiltin', function () {
    it('sets built-in plugin paths through a request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.builtin.set', ['paths' => ['/opt/copilot/plugins']])
            ->andReturn([]);

        (new PendingServerPluginsBuiltin($client))->set(
            new PluginsBuiltinSetRequest(['/opt/copilot/plugins']),
        );
    });
});

describe('PendingServerPluginsMarketplaces', function () {
    it('lists registered marketplaces and decodes their metadata', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.marketplaces.list', [])
            ->andReturn([
                'marketplaces' => [[
                    'name' => 'official',
                    'source' => 'GitHub: github/copilot-plugins',
                    'available' => true,
                    'isDefault' => false,
                    'managed' => false,
                ]],
            ]);

        $result = (new PendingServerPluginsMarketplaces($client))->list();

        expect($result)->toBeInstanceOf(MarketplaceListResult::class)
            ->and($result->marketplaces[0]->name)->toBe('official')
            ->and($result->marketplaces[0]->available)->toBeTrue();
    });

    it('adds a marketplace using the shared add request type', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.marketplaces.add', [
                'source' => 'github/copilot-plugins',
                'workingDirectory' => '/workspace',
            ])
            ->andReturn(['name' => 'official']);

        $result = (new PendingServerPluginsMarketplaces($client))->add(
            new PluginsMarketplacesAddRequest('github/copilot-plugins', '/workspace'),
        );

        expect($result)->toBeInstanceOf(MarketplaceAddResult::class)
            ->and($result->name)->toBe('official');
    });

    it('removes a marketplace and preserves a false force flag', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.marketplaces.remove', [
                'name' => 'official',
                'force' => false,
            ])
            ->andReturn([
                'removed' => false,
                'dependentPlugins' => ['eslint'],
            ]);

        $result = (new PendingServerPluginsMarketplaces($client))->remove(
            new PluginsMarketplacesRemoveRequest('official', false),
        );

        expect($result)->toBeInstanceOf(MarketplaceRemoveResult::class)
            ->and($result->removed)->toBeFalse()
            ->and($result->dependentPlugins)->toBe(['eslint']);
    });

    it('browses a marketplace and returns its flexible result as an array', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.marketplaces.browse', ['name' => 'official'])
            ->andReturn(['plugins' => [['name' => 'eslint']]]);

        $result = (new PendingServerPluginsMarketplaces($client))->browse(
            new PluginsMarketplacesBrowseRequest('official'),
        );

        expect($result)->toBe(['plugins' => [['name' => 'eslint']]]);
    });

    it('refreshes all marketplaces when no name is provided', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.marketplaces.refresh', [])
            ->andReturn([
                'results' => [[
                    'name' => 'official',
                    'success' => true,
                ]],
            ]);

        $result = (new PendingServerPluginsMarketplaces($client))->refresh();

        expect($result)->toBeInstanceOf(MarketplaceRefreshResult::class)
            ->and($result->results[0]->name)->toBe('official')
            ->and($result->results[0]->success)->toBeTrue();
    });

    it('refreshes one marketplace when a typed name is supplied', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('plugins.marketplaces.refresh', ['name' => 'official'])
            ->andReturn(['results' => []]);

        $result = (new PendingServerPluginsMarketplaces($client))->refresh(
            new PluginsMarketplacesRefreshRequest('official'),
        );

        expect($result->results)->toBeEmpty();
    });
});
