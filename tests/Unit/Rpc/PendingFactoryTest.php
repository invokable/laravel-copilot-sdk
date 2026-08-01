<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingFactory;
use Revolution\Copilot\Rpc\PendingFactoryJournal;
use Revolution\Copilot\Types\Rpc\FactoryAckResult;
use Revolution\Copilot\Types\Rpc\FactoryAgentRequest;
use Revolution\Copilot\Types\Rpc\FactoryAgentResult;
use Revolution\Copilot\Types\Rpc\FactoryCancelRequest;
use Revolution\Copilot\Types\Rpc\FactoryGetRunProgressRequest;
use Revolution\Copilot\Types\Rpc\FactoryGetRunRequest;
use Revolution\Copilot\Types\Rpc\FactoryListRunsResult;
use Revolution\Copilot\Types\Rpc\FactoryLogLine;
use Revolution\Copilot\Types\Rpc\FactoryLogRequest;
use Revolution\Copilot\Types\Rpc\FactoryProgressPage;
use Revolution\Copilot\Types\Rpc\FactoryResumeRequest;
use Revolution\Copilot\Types\Rpc\FactoryResumeResult;
use Revolution\Copilot\Types\Rpc\FactoryRunDetail;
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
            executionToken: 'token-1',
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
            executionToken: 'token-1',
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

    it('calls session.factory.resume and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.factory.resume',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['runId'] === 'run-1'),
            )
            ->andReturn(['factoryName' => 'my-factory', 'run' => ['runId' => 'run-1', 'status' => 'running']]);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->resume(new FactoryResumeRequest(runId: 'run-1'));

        expect($result)->toBeInstanceOf(FactoryResumeResult::class)
            ->and($result->factoryName)->toBe('my-factory')
            ->and($result->run->runId)->toBe('run-1');
    });

    it('calls session.factory.listRuns and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.factory.listRuns', ['sessionId' => 'session-xyz'])
            ->andReturn(['runs' => []]);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->listRuns();

        expect($result)->toBeInstanceOf(FactoryListRunsResult::class)
            ->and($result->runs)->toBe([]);
    });

    it('calls session.factory.getRunDetail and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.factory.getRunDetail', ['runId' => 'run-1', 'sessionId' => 'session-xyz'])
            ->andReturn([
                'runId' => 'run-1',
                'factoryName' => 'my-factory',
                'description' => 'A run',
                'status' => 'running',
                'revision' => 1,
                'createdAt' => 1000,
                'startedAt' => 1001,
                'updatedAt' => 1002,
                'completedAt' => null,
                'currentPhase' => null,
                'declaredPhaseCount' => 0,
                'liveAgentCount' => 0,
                'totalSpawnedAgentCount' => 0,
                'consumed' => ['activeMs' => 0, 'subagents' => 0, 'nanoAiu' => 0],
                'declaredLimits' => [],
                'approved' => null,
                'observedAt' => 1002,
                'activeSegmentStartedAt' => null,
                'terminal' => null,
                'phases' => [],
                'agents' => [],
                'progress' => ['records' => [], 'oldestSeq' => null, 'newestSeq' => null, 'hasMoreOlder' => false, 'hasMoreNewer' => false, 'revision' => 1],
            ]);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->getRunDetail(new FactoryGetRunRequest(runId: 'run-1'));

        expect($result)->toBeInstanceOf(FactoryRunDetail::class)
            ->and($result->runId)->toBe('run-1')
            ->and($result->status->value)->toBe('running');
    });

    it('calls session.factory.getRunProgress and returns page', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.factory.getRunProgress',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'
                    && $params['runId'] === 'run-1'
                    && $params['limit'] === 50),
            )
            ->andReturn(['records' => [], 'oldestSeq' => null, 'newestSeq' => null, 'hasMoreOlder' => false, 'hasMoreNewer' => false, 'revision' => 2]);

        $pending = new PendingFactory($client, 'session-xyz');
        $result = $pending->getRunProgress(new FactoryGetRunProgressRequest(runId: 'run-1', limit: 50));

        expect($result)->toBeInstanceOf(FactoryProgressPage::class)
            ->and($result->revision)->toBe(2)
            ->and($result->records)->toBe([]);
    });
});
