<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryRunLimits;
use Revolution\Copilot\Types\Rpc\RunOptions;

describe('RunOptions', function () {
    it('can be created from array', function () {
        $options = RunOptions::fromArray([
            'limits' => ['maxTotalSubagents' => 5],
            'resumeFromRunId' => 'run-1',
        ]);

        expect($options->limits)->toBeInstanceOf(FactoryRunLimits::class)
            ->and($options->limits->maxTotalSubagents)->toBe(5)
            ->and($options->resumeFromRunId)->toBe('run-1');
    });

    it('defaults to null values', function () {
        $options = RunOptions::fromArray([]);

        expect($options->limits)->toBeNull()
            ->and($options->resumeFromRunId)->toBeNull();
    });

    it('converts to array correctly', function () {
        $options = new RunOptions(
            limits: new FactoryRunLimits(maxTotalSubagents: 5),
            resumeFromRunId: 'run-1',
        );

        expect($options->toArray())->toBe([
            'limits' => ['maxTotalSubagents' => 5],
            'resumeFromRunId' => 'run-1',
        ]);
    });
});
