<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunStatus;
use Revolution\Copilot\Types\Rpc\FactoryRunFailure;
use Revolution\Copilot\Types\Rpc\FactoryRunResult;

describe('FactoryRunResult', function () {
    it('can be created from array', function () {
        $result = FactoryRunResult::fromArray([
            'runId' => 'run-1',
            'status' => 'completed',
            'result' => ['a' => 1],
        ]);

        expect($result->runId)->toBe('run-1')
            ->and($result->status)->toBe(FactoryRunStatus::COMPLETED)
            ->and($result->result)->toBe(['a' => 1]);
    });

    it('handles failure details', function () {
        $result = FactoryRunResult::fromArray([
            'runId' => 'run-1',
            'status' => 'error',
            'failure' => [
                'runId' => 'run-1',
                'type' => 'factory_limit_reached',
                'kind' => 'timeout',
                'value' => 1000.0,
            ],
        ]);

        expect($result->failure)->toBeInstanceOf(FactoryRunFailure::class)
            ->and($result->failure->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $result = new FactoryRunResult(runId: 'run-1', status: FactoryRunStatus::RUNNING);

        expect($result->toArray())->toBe([
            'runId' => 'run-1',
            'status' => 'running',
        ]);
    });
});
