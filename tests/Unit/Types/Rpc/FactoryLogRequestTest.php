<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryLogLine;
use Revolution\Copilot\Types\Rpc\FactoryLogRequest;

describe('FactoryLogRequest', function () {
    it('can be created from array', function () {
        $request = FactoryLogRequest::fromArray([
            'executionToken' => 'token-1',
            'lines' => [
                ['kind' => 'log', 'seq' => 1, 'text' => 'Starting'],
            ],
            'runId' => 'run-1',
        ]);

        expect($request->lines)->toHaveCount(1)
            ->and($request->executionToken)->toBe('token-1')
            ->and($request->lines[0])->toBeInstanceOf(FactoryLogLine::class)
            ->and($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryLogRequest(
            executionToken: 'token-1',
            lines: [new FactoryLogLine(kind: 'log', seq: 1, text: 'Starting')],
            runId: 'run-1',
        );

        expect($request->toArray())->toBe([
            'executionToken' => 'token-1',
            'lines' => [
                ['kind' => 'log', 'seq' => 1, 'text' => 'Starting'],
            ],
            'runId' => 'run-1',
        ]);
    });
});
