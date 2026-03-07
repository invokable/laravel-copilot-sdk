<?php

declare(strict_types=1);

use Revolution\Copilot\Rpc\PendingAgent;
use Revolution\Copilot\Rpc\PendingCompaction;
use Revolution\Copilot\Rpc\PendingFleet;
use Revolution\Copilot\Rpc\PendingMode;
use Revolution\Copilot\Rpc\PendingModel;
use Revolution\Copilot\Rpc\PendingPlan;
use Revolution\Copilot\Rpc\PendingPermissions;
use Revolution\Copilot\Rpc\PendingTools;
use Revolution\Copilot\Rpc\PendingWorkspace;
use Revolution\Copilot\Rpc\SessionRpc;

describe('SessionRpc', function () {
    it('returns PendingModel from model()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->model())->toBeInstanceOf(PendingModel::class);
    });

    it('returns PendingMode from mode()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->mode())->toBeInstanceOf(PendingMode::class);
    });

    it('returns PendingPlan from plan()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->plan())->toBeInstanceOf(PendingPlan::class);
    });

    it('returns PendingWorkspace from workspace()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->workspace())->toBeInstanceOf(PendingWorkspace::class);
    });

    it('returns PendingFleet from fleet()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->fleet())->toBeInstanceOf(PendingFleet::class);
    });

    it('returns PendingAgent from agent()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->agent())->toBeInstanceOf(PendingAgent::class);
    });

    it('returns PendingCompaction from compaction()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->compaction())->toBeInstanceOf(PendingCompaction::class);
    });

    it('returns PendingSessionTools from tools()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->tools())->toBeInstanceOf(PendingTools::class);
    });

    it('returns PendingSessionPermissions from permissions()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->permissions())->toBeInstanceOf(PendingPermissions::class);
    });
});

function createMockSessionRpcClient(): \Revolution\Copilot\JsonRpc\JsonRpcClient
{
    $transport = new \Revolution\Copilot\Transport\StdioTransport(
        fopen('php://memory', 'r'),
        fopen('php://memory', 'w'),
    );

    return new \Revolution\Copilot\JsonRpc\JsonRpcClient($transport);
}
