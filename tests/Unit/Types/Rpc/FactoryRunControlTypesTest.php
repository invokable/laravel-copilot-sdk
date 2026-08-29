<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryGetRunProgressRequest;
use Revolution\Copilot\Types\Rpc\FactoryListRunsRequest;
use Revolution\Copilot\Types\Rpc\FactoryListRunsResult;
use Revolution\Copilot\Types\Rpc\FactoryResumeRequest;
use Revolution\Copilot\Types\Rpc\FactoryResumeResult;
use Revolution\Copilot\Types\Rpc\FactoryRunLimits;
use Revolution\Copilot\Types\Rpc\FactoryRunResult;
use Revolution\Copilot\Types\Rpc\FactoryRunSummary;
use Revolution\Copilot\Types\Rpc\FactoryRunUpdatedData;

describe('FactoryResumeRequest', function () {
    it('nests optional limits', function () {
        $request = FactoryResumeRequest::fromArray([
            'runId' => 'run-1',
            'limits' => ['maxTotalSubagents' => 4],
            'notifyOnComplete' => false,
            'logPhaseNames' => true,
        ]);

        expect($request->runId)->toBe('run-1')
            ->and($request->limits)->toBeInstanceOf(FactoryRunLimits::class)
            ->and($request->logPhaseNames)->toBeTrue()
            ->and($request->notifyOnComplete)->toBeFalse()
            ->and($request->toArray())->toBe([
                'runId' => 'run-1',
                'limits' => ['maxTotalSubagents' => 4],
                'notifyOnComplete' => false,
                'logPhaseNames' => true,
            ]);
    });

    it('omits limits when absent', function () {
        expect(FactoryResumeRequest::fromArray(['runId' => 'run-1'])->toArray())->toBe(['runId' => 'run-1']);
    });
});

describe('FactoryResumeResult', function () {
    it('wraps the run envelope', function () {
        $result = FactoryResumeResult::fromArray([
            'factoryName' => 'greeter',
            'run' => ['runId' => 'run-1', 'status' => 'running'],
        ]);

        expect($result->factoryName)->toBe('greeter')
            ->and($result->run)->toBeInstanceOf(FactoryRunResult::class)
            ->and($result->toArray()['run']['runId'])->toBe('run-1');
    });
});

describe('FactoryListRunsRequest', function () {
    it('is an empty payload', function () {
        expect(FactoryListRunsRequest::fromArray([])->toArray())->toBe([]);
    });
});

describe('FactoryListRunsResult', function () {
    it('hydrates run summaries', function () {
        $result = FactoryListRunsResult::fromArray([
            'runs' => [[
                'runId' => 'run-1', 'factoryName' => 'g', 'description' => 'd', 'status' => 'running',
                'revision' => 1, 'createdAt' => 1, 'startedAt' => null, 'updatedAt' => 1, 'completedAt' => null,
                'currentPhase' => null, 'declaredPhaseCount' => 0, 'liveAgentCount' => 0, 'totalSpawnedAgentCount' => 0,
                'consumed' => ['activeMs' => 0, 'subagents' => 0, 'nanoAiu' => 0], 'declaredLimits' => [],
                'approved' => null, 'observedAt' => 1, 'activeSegmentStartedAt' => null, 'terminal' => null,
            ]],
        ]);

        expect($result->runs)->toHaveCount(1)
            ->and($result->runs[0])->toBeInstanceOf(FactoryRunSummary::class)
            ->and($result->runs[0]->runId)->toBe('run-1');
    });
});

describe('FactoryGetRunProgressRequest', function () {
    it('keeps only provided optional filters', function () {
        $request = FactoryGetRunProgressRequest::fromArray([
            'runId' => 'run-1',
            'afterSeq' => 5,
            'limit' => 20,
        ]);

        expect($request->toArray())->toBe([
            'runId' => 'run-1',
            'afterSeq' => 5,
            'limit' => 20,
        ]);
    });
});

describe('FactoryRunUpdatedData', function () {
    it('roundtrips', function () {
        $data = ['revision' => 9, 'runId' => 'run-1'];

        expect(FactoryRunUpdatedData::fromArray($data)->toArray())->toBe($data);
    });
});
