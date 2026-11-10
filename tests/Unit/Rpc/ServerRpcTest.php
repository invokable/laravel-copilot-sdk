<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerAccount;
use Revolution\Copilot\Rpc\PendingServerAgentRegistry;
use Revolution\Copilot\Rpc\PendingServerAzureMl;
use Revolution\Copilot\Rpc\PendingServerCatalog;
use Revolution\Copilot\Rpc\PendingServerCloudTasks;
use Revolution\Copilot\Rpc\PendingServerCommands;
use Revolution\Copilot\Rpc\PendingServerConnectors;
use Revolution\Copilot\Rpc\PendingServerDecisions;
use Revolution\Copilot\Rpc\PendingServerGit;
use Revolution\Copilot\Rpc\PendingServerGitHubFetch;
use Revolution\Copilot\Rpc\PendingServerGitHubOwners;
use Revolution\Copilot\Rpc\PendingServerGitHubRelease;
use Revolution\Copilot\Rpc\PendingServerGitHubRepository;
use Revolution\Copilot\Rpc\PendingServerHooks;
use Revolution\Copilot\Rpc\PendingServerMcpConfig;
use Revolution\Copilot\Rpc\PendingServerModels;
use Revolution\Copilot\Rpc\PendingServerNetwork;
use Revolution\Copilot\Rpc\PendingServerPlugins;
use Revolution\Copilot\Rpc\PendingServerSandbox;
use Revolution\Copilot\Rpc\PendingServerSandboxProxyCa;
use Revolution\Copilot\Rpc\PendingServerSessionFs;
use Revolution\Copilot\Rpc\PendingServerSessions;
use Revolution\Copilot\Rpc\PendingServerSettings;
use Revolution\Copilot\Rpc\PendingServerSkills;
use Revolution\Copilot\Rpc\PendingServerTools;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Transport\StdioTransport;

describe('ServerRpc', function () {
    it('returns PendingModels from models()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->models())->toBeInstanceOf(PendingServerModels::class);
    });

    it('returns PendingServerDecisions from decisions()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->decisions())->toBeInstanceOf(PendingServerDecisions::class);
    });

    it('returns PendingServerAzureMl from azureMl()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->azureMl())->toBeInstanceOf(PendingServerAzureMl::class);
    });

    it('returns PendingTools from tools()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->tools())->toBeInstanceOf(PendingServerTools::class);
    });

    it('returns PendingAccount from account()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->account())->toBeInstanceOf(PendingServerAccount::class);
    });

    it('returns PendingServerMcpConfig from mcp()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->mcp())->toBeInstanceOf(PendingServerMcpConfig::class);
    });

    it('returns PendingServerSessionFs from sessionFs()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->sessionFs())->toBeInstanceOf(PendingServerSessionFs::class);
    });

    it('returns PendingServerSessions from sessions()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->sessions())->toBeInstanceOf(PendingServerSessions::class);
    });

    it('returns PendingServerSkills from skills()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->skills())->toBeInstanceOf(PendingServerSkills::class);
    });

    it('returns PendingServerPlugins from plugins()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->plugins())->toBeInstanceOf(PendingServerPlugins::class);
    });

    it('returns PendingServerCatalog from catalog()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->catalog())->toBeInstanceOf(PendingServerCatalog::class);
    });

    it('returns PendingServerCloudTasks from cloudTasks()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->cloudTasks())->toBeInstanceOf(PendingServerCloudTasks::class);
    });

    it('registers the extension launch provider', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->with('registerExtensionLaunchProvider', [])
            ->once()
            ->andReturn([]);

        (new ServerRpc($client))->registerExtensionLaunchProvider();
    });

    it('exposes new server groups and dispatches their RPC methods', function () {
        $rpc = new ServerRpc(new JsonRpcClient(new StdioTransport(
            fopen('php://memory', 'r'),
            fopen('php://memory', 'w'),
        )));

        expect($rpc->connectors())->toBeInstanceOf(PendingServerConnectors::class)
            ->and($rpc->git())->toBeInstanceOf(PendingServerGit::class)
            ->and($rpc->gitHubRepository())->toBeInstanceOf(PendingServerGitHubRepository::class)
            ->and($rpc->gitHubFetch())->toBeInstanceOf(PendingServerGitHubFetch::class)
            ->and($rpc->gitHubRelease())->toBeInstanceOf(PendingServerGitHubRelease::class)
            ->and($rpc->gitHubOwners())->toBeInstanceOf(PendingServerGitHubOwners::class)
            ->and($rpc->commands())->toBeInstanceOf(PendingServerCommands::class)
            ->and($rpc->hooks())->toBeInstanceOf(PendingServerHooks::class)
            ->and($rpc->sandbox())->toBeInstanceOf(PendingServerSandbox::class)
            ->and($rpc->sandboxProxyCa())->toBeInstanceOf(PendingServerSandboxProxyCa::class)
            ->and($rpc->settings())->toBeInstanceOf(PendingServerSettings::class)
            ->and($rpc->network())->toBeInstanceOf(PendingServerNetwork::class);

        expect($rpc->agentRegistry())->toBeInstanceOf(PendingServerAgentRegistry::class);
    });
});

function createMockRpcClient(): JsonRpcClient
{
    $transport = new StdioTransport(
        fopen('php://memory', 'r'),
        fopen('php://memory', 'w'),
    );

    return new JsonRpcClient($transport);
}
