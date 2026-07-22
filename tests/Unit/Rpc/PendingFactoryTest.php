<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingFactory;
use Revolution\Copilot\Rpc\PendingFactoryJournal;
use Revolution\Copilot\Types\Rpc\FactoryAckResult;
use Revolution\Copilot\Types\Rpc\FactoryAgentRequest;
use Revolution\Copilot\Types\Rpc\FactoryAgentResult;
use Revolution\Copilot\Types\Rpc\FactoryCancelRequest;
use Revolution\Copilot\Types\Rpc\FactoryGetRunRequest;
use Revolution\Copilot\Types\Rpc\FactoryLogLine;
use Revolution\Copilot\Types\Rpc\FactoryLogRequest;
use Revolution\Copilot\Types\Rpc\FactoryRunRequest;
use Revolution\Copilot\Types\Rpc\FactoryRunResult;

describe('PendingFactory', function () {
    it('calls session.factory.run and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.factory.run',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['name'] === 'my-factory'),
            )
            ->andReturn(['runId' => 'run-1', 'status' => 'running']);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->run(new FactoryRunRequest(args: null, name: 'my-factory'));

        expect($result)->toBeInstanceOf(FactoryRunResult::class)
            ->and($result->runId)->toBe('run-1');
    });

    it('calls session.factory.getRun and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.factory.getRun', ['runId' => 'run-1', 'sessionId' => 'session-xyz'])
            ->andReturn(['runId' => 'run-1', 'status' => 'completed']);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->getRun(new FactoryGetRunRequest(runId: 'run-1'));

        expect($result)->toBeInstanceOf(FactoryRunResult::class)
            ->and($result->status->value)->toBe('completed');
    });

    it('calls session.factory.cancel and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.factory.cancel', ['runId' => 'run-1', 'sessionId' => 'session-xyz'])
            ->andReturn(['runId' => 'run-1', 'status' => 'cancelled']);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->cancel(new FactoryCancelRequest(runId: 'run-1'));

        expect($result)->toBeInstanceOf(FactoryRunResult::class)
            ->and($result->status->value)->toBe('cancelled');
    });

    it('calls session.factory.log and returns ack', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.factory.log',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['runId'] === 'run-1'),
            )
            ->andReturn([]);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->log(new FactoryLogRequest(
            lines: [new FactoryLogLine(kind: 'log', seq: 1, text: 'Starting')],
            runId: 'run-1',
        ));

        expect($result)->toBeInstanceOf(FactoryAckResult::class);
    });

    it('calls session.factory.agent and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.factory.agent',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['factoryRunId'] === 'run-1'),
            )
            ->andReturn(['result' => 'done']);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->agent(new FactoryAgentRequest(
            factoryRunId: 'run-1',
            opts: [],
            prompt: 'Do something',
        ));

        expect($result)->toBeInstanceOf(FactoryAgentResult::class)
            ->and($result->result)->toBe('done');
    });

    it('returns a PendingFactoryJournal instance', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $pending = new PendingFactory($client, 'session-xyz');

        expect($pending->journal())->toBeInstanceOf(PendingFactoryJournal::class);
    });
});
