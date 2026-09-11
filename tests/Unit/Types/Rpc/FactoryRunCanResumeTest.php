<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunStatus;
use Revolution\Copilot\Types\Rpc\FactoryRunDetail;
use Revolution\Copilot\Types\Rpc\FactoryRunSummary;

function factoryRunBaseData(): array
{
    return [
        'runId' => 'run-1',
        'factoryName' => 'my-factory',
        'description' => 'A run',
        'status' => 'running',
        'revision' => 1,
        'createdAt' => 1000,
        'updatedAt' => 1000,
        'declaredPhaseCount' => 0,
        'liveAgentCount' => 0,
        'totalSpawnedAgentCount' => 0,
        'consumed' => ['activeMs' => 0, 'subagents' => 0, 'nanoAiu' => 0],
        'declaredLimits' => [],
        'observedAt' => 1000,
    ];
}

describe('FactoryRunSummary canResume', function () {
    it('defaults canResume to false when missing', function () {
        $summary = FactoryRunSummary::fromArray(factoryRunBaseData());

        expect($summary->canResume)->toBeFalse();
    });

    it('reads canResume from array', function () {
        $summary = FactoryRunSummary::fromArray([...factoryRunBaseData(), 'canResume' => true]);

        expect($summary->canResume)->toBeTrue()
            ->and($summary->toArray()['canResume'])->toBeTrue();
    });

    it('supports the paused status', function () {
        $summary = FactoryRunSummary::fromArray([...factoryRunBaseData(), 'status' => 'paused']);

        expect($summary->status)->toBe(FactoryRunStatus::PAUSED);
    });
});

describe('FactoryRunDetail canResume', function () {
    it('defaults canResume to false when missing', function () {
        $detail = FactoryRunDetail::fromArray([
            ...factoryRunBaseData(),
            'phases' => [],
            'agents' => [],
            'progress' => [],
        ]);

        expect($detail->canResume)->toBeFalse();
    });

    it('reads canResume from array', function () {
        $detail = FactoryRunDetail::fromArray([
            ...factoryRunBaseData(),
            'canResume' => true,
            'phases' => [],
            'agents' => [],
            'progress' => [],
        ]);

        expect($detail->canResume)->toBeTrue()
            ->and($detail->toArray()['canResume'])->toBeTrue();
    });
});
