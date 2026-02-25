<?php

declare(strict_types=1);

use Revolution\Copilot\Rpc\PendingAccount;
use Revolution\Copilot\Rpc\PendingModels;
use Revolution\Copilot\Rpc\PendingTools;
use Revolution\Copilot\Rpc\ServerRpc;

describe('ServerRpc', function () {
    it('returns PendingModels from models()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->models())->toBeInstanceOf(PendingModels::class);
    });

    it('returns PendingTools from tools()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->tools())->toBeInstanceOf(PendingTools::class);
    });

    it('returns PendingAccount from account()', function () {
        $rpc = new ServerRpc(createMockRpcClient());

        expect($rpc->account())->toBeInstanceOf(PendingAccount::class);
    });
});

function createMockRpcClient(): \Revolution\Copilot\JsonRpc\JsonRpcClient
{
    $transport = new \Revolution\Copilot\Transport\StdioTransport(
        fopen('php://memory', 'r'),
        fopen('php://memory', 'w'),
    );

    return new \Revolution\Copilot\JsonRpc\JsonRpcClient($transport);
}
