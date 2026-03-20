<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingAgent;
use Revolution\Copilot\Types\Rpc\SessionAgentGetCurrentResult;
use Revolution\Copilot\Types\Rpc\SessionAgentListResult;
use Revolution\Copilot\Types\Rpc\SessionAgentReloadResult;
use Revolution\Copilot\Types\Rpc\SessionAgentSelectParams;
use Revolution\Copilot\Types\Rpc\SessionAgentSelectResult;

describe('PendingAgent', function () {
    it('calls session.agent.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.agent.list', ['sessionId' => 'session-abc'])
            ->andReturn([
                'agents' => [
                    [
                        'name' => 'test-agent',
                        'displayName' => 'Test Agent',
                        'description' => 'A test agent',
                    ],
                ],
            ]);

        $pending = new PendingAgent($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(SessionAgentListResult::class)
            ->and($result->agents)->toHaveCount(1)
            ->and($result->agents[0]->name)->toBe('test-agent');
    });

    it('calls session.agent.getCurrent and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.agent.getCurrent', ['sessionId' => 'session-abc'])
            ->andReturn([
                'agent' => [
                    'name' => 'current-agent',
                    'displayName' => 'Current Agent',
                    'description' => 'The current agent',
                ],
            ]);

        $pending = new PendingAgent($client, 'session-abc');
        $result = $pending->getCurrent();

        expect($result)->toBeInstanceOf(SessionAgentGetCurrentResult::class)
            ->and($result->agent->name)->toBe('current-agent');
    });

    it('calls session.agent.select with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.agent.select',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['name'] === 'my-agent'),
            )
            ->andReturn([
                'agent' => [
                    'name' => 'my-agent',
                    'displayName' => 'My Agent',
                    'description' => 'Selected agent',
                ],
            ]);

        $pending = new PendingAgent($client, 'session-abc');
        $result = $pending->select(new SessionAgentSelectParams(name: 'my-agent'));

        expect($result)->toBeInstanceOf(SessionAgentSelectResult::class)
            ->and($result->agent->name)->toBe('my-agent');
    });

    it('calls session.agent.deselect', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.agent.deselect', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingAgent($client, 'session-abc');
        $result = $pending->deselect();

        expect($result)->toBe([]);
    });

    it('calls session.agent.reload and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.agent.reload', ['sessionId' => 'session-abc'])
            ->andReturn([
                'agents' => [
                    [
                        'name' => 'reloaded-agent',
                        'displayName' => 'Reloaded Agent',
                        'description' => 'An agent after reload',
                    ],
                ],
            ]);

        $pending = new PendingAgent($client, 'session-abc');
        $result = $pending->reload();

        expect($result)->toBeInstanceOf(SessionAgentReloadResult::class)
            ->and($result->agents)->toHaveCount(1)
            ->and($result->agents[0]->name)->toBe('reloaded-agent');
    });

    it('calls session.agent.reload with empty agents list', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.agent.reload', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingAgent($client, 'session-abc');
        $result = $pending->reload();

        expect($result)->toBeInstanceOf(SessionAgentReloadResult::class)
            ->and($result->agents)->toBe([]);
    });
});
