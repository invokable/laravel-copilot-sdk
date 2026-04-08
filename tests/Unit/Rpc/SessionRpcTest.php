<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingAgent;
use Revolution\Copilot\Rpc\PendingCommands;
use Revolution\Copilot\Rpc\PendingExtensions;
use Revolution\Copilot\Rpc\PendingFleet;
use Revolution\Copilot\Rpc\PendingHistory;
use Revolution\Copilot\Rpc\PendingLog;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingMode;
use Revolution\Copilot\Rpc\PendingModel;
use Revolution\Copilot\Rpc\PendingPermissions;
use Revolution\Copilot\Rpc\PendingPlan;
use Revolution\Copilot\Rpc\PendingPlugins;
use Revolution\Copilot\Rpc\PendingSkills;
use Revolution\Copilot\Rpc\PendingTools;
use Revolution\Copilot\Rpc\PendingUi;
use Revolution\Copilot\Rpc\PendingWorkspace;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Transport\StdioTransport;

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

    it('returns PendingHistory from history()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->history())->toBeInstanceOf(PendingHistory::class);
    });

    it('returns PendingSessionTools from tools()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->tools())->toBeInstanceOf(PendingTools::class);
    });

    it('returns PendingSessionPermissions from permissions()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->permissions())->toBeInstanceOf(PendingPermissions::class);
    });

    it('returns PendingLog from log()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->log())->toBeInstanceOf(PendingLog::class);
    });

    it('returns PendingSkills from skills()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->skills())->toBeInstanceOf(PendingSkills::class);
    });

    it('returns PendingMcp from mcp()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->mcp())->toBeInstanceOf(PendingMcp::class);
    });

    it('returns PendingPlugins from plugins()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->plugins())->toBeInstanceOf(PendingPlugins::class);
    });

    it('returns PendingExtensions from extensions()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->extensions())->toBeInstanceOf(PendingExtensions::class);
    });

    it('returns PendingCommands from commands()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->commands())->toBeInstanceOf(PendingCommands::class);
    });

    it('returns PendingUi from ui()', function () {
        $rpc = new SessionRpc(createMockSessionRpcClient(), 'test-session');

        expect($rpc->ui())->toBeInstanceOf(PendingUi::class);
    });
});

function createMockSessionRpcClient(): JsonRpcClient
{
    $transport = new StdioTransport(
        fopen('php://memory', 'r'),
        fopen('php://memory', 'w'),
    );

    return new JsonRpcClient($transport);
}
