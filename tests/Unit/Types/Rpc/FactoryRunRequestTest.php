<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryRunRequest;
use Revolution\Copilot\Types\Rpc\RunOptions;

describe('FactoryRunRequest', function () {
    it('can be created from array', function () {
        $request = FactoryRunRequest::fromArray([
            'args' => ['a' => 1],
            'name' => 'my-factory',
            'options' => ['resumeFromRunId' => 'run-1'],
        ]);

        expect($request->args)->toBe(['a' => 1])
            ->and($request->name)->toBe('my-factory')
            ->and($request->options)->toBeInstanceOf(RunOptions::class)
            ->and($request->options->resumeFromRunId)->toBe('run-1');
    });

    it('defaults options to null', function () {
        $request = FactoryRunRequest::fromArray(['name' => 'my-factory']);

        expect($request->options)->toBeNull();
    });

    it('converts to array correctly', function () {
        $request = new FactoryRunRequest(
            args: ['a' => 1],
            name: 'my-factory',
            options: new RunOptions(resumeFromRunId: 'run-1'),
        );

        expect($request->toArray())->toBe([
            'args' => ['a' => 1],
            'name' => 'my-factory',
            'options' => ['resumeFromRunId' => 'run-1'],
        ]);
    });
});
