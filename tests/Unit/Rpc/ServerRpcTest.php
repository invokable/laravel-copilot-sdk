<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCatalog;
use Revolution\Copilot\Rpc\PendingServerAccount;
use Revolution\Copilot\Rpc\PendingServerCommands;
use Revolution\Copilot\Rpc\PendingServerConnectors;
use Revolution\Copilot\Rpc\PendingServerGit;
use Revolution\Copilot\Rpc\PendingServerGitHubOwners;
use Revolution\Copilot\Rpc\PendingServerGitHubRepository;
use Revolution\Copilot\Rpc\PendingServerHooks;
use Revolution\Copilot\Rpc\PendingServerMcpConfig;
use Revolution\Copilot\Rpc\PendingServerModels;
use Revolution\Copilot\Rpc\PendingServerPlugins;
use Revolution\Copilot\Rpc\PendingServerSandbox;
use Revolution\Copilot\Rpc\PendingServerSandboxProxyCa;
use Revolution\Copilot\Rpc\PendingServerSessionFs;
use Revolution\Copilot\Rpc\PendingServerSkills;
use Revolution\Copilot\Rpc\PendingServerTools;
use Revolution\Copilot\Rpc\PendingSessions;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Transport\StdioTransport;

describe('ServerRpc', function () {
    it('returns PendingModels from models()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->models())->toBeInstanceOf(PendingServerModels::class);
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

    it('returns PendingSessions from sessions()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->sessions())->toBeInstanceOf(PendingSessions::class);
    });

    it('returns PendingServerSkills from skills()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->skills())->toBeInstanceOf(PendingServerSkills::class);
    });

    it('returns PendingServerPlugins from plugins()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->plugins())->toBeInstanceOf(PendingServerPlugins::class);
    });

    it('returns PendingCatalog from catalog()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->catalog())->toBeInstanceOf(PendingCatalog::class);
    });

    it('exposes new server groups and dispatches their RPC methods', function () {
        $rpc = new ServerRpc(new JsonRpcClient(new StdioTransport(
            fopen('php://memory', 'r'),
            fopen('php://memory', 'w'),
        )));

        expect($rpc->connectors())->toBeInstanceOf(PendingServerConnectors::class)
            ->and($rpc->git())->toBeInstanceOf(PendingServerGit::class)
            ->and($rpc->gitHubRepository())->toBeInstanceOf(PendingServerGitHubRepository::class)
            ->and($rpc->gitHubOwners())->toBeInstanceOf(PendingServerGitHubOwners::class)
            ->and($rpc->commands())->toBeInstanceOf(PendingServerCommands::class)
            ->and($rpc->hooks())->toBeInstanceOf(PendingServerHooks::class)
            ->and($rpc->sandbox())->toBeInstanceOf(PendingServerSandbox::class)
            ->and($rpc->sandboxProxyCa())->toBeInstanceOf(PendingServerSandboxProxyCa::class);
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
