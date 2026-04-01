<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerAccount;
use Revolution\Copilot\Rpc\PendingServerMcpConfig;
use Revolution\Copilot\Rpc\PendingServerModels;
use Revolution\Copilot\Rpc\PendingServerSessionFs;
use Revolution\Copilot\Rpc\PendingServerTools;
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
});

function createMockRpcClient(): JsonRpcClient
{
    $transport = new StdioTransport(
        fopen('php://memory', 'r'),
        fopen('php://memory', 'w'),
    );

    return new JsonRpcClient($transport);
}
