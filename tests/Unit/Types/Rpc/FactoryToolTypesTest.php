<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryRunLimits;
use Revolution\Copilot\Types\Rpc\FactoryToolResumeRequest;
use Revolution\Copilot\Types\Rpc\FactoryToolRunOptions;
use Revolution\Copilot\Types\Rpc\FactoryToolRunRequest;

describe('FactoryToolRunRequest', function () {
    it('hydrates nested options and tool metadata', function () {
        $request = FactoryToolRunRequest::fromArray([
            'name' => 'my-factory',
            'args' => ['prompt' => 'hello'],
            'options' => [
                'limits' => ['maxTotalSubagents' => 2],
                'resumeFromRunId' => 'run-1',
            ],
            'toolCallId' => 'tool-1',
        ]);

        expect($request->name)->toBe('my-factory')
            ->and($request->args)->toBe(['prompt' => 'hello'])
            ->and($request->options)->toBeInstanceOf(FactoryToolRunOptions::class)
            ->and($request->options->limits)->toBeInstanceOf(FactoryRunLimits::class)
            ->and($request->toolCallId)->toBe('tool-1')
            ->and($request->toArray())->toBe([
                'args' => ['prompt' => 'hello'],
                'name' => 'my-factory',
                'options' => [
                    'limits' => ['maxTotalSubagents' => 2],
                    'resumeFromRunId' => 'run-1',
                ],
                'toolCallId' => 'tool-1',
            ]);
    });
});

describe('FactoryToolResumeRequest', function () {
    it('roundtrips optional limits and tool metadata', function () {
        $request = FactoryToolResumeRequest::fromArray([
            'runId' => 'run-1',
            'limits' => ['maxConcurrentSubagents' => 1],
            'toolCallId' => 'tool-1',
        ]);

        expect($request->limits)->toBeInstanceOf(FactoryRunLimits::class)
            ->and($request->toolCallId)->toBe('tool-1')
            ->and($request->toArray())->toBe([
                'runId' => 'run-1',
                'limits' => ['maxConcurrentSubagents' => 1],
                'toolCallId' => 'tool-1',
            ]);
    });
});
