<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryAgentOptions;
use Revolution\Copilot\Types\Rpc\FactoryAgentRequest;

describe('FactoryAgentRequest', function () {
    it('can be created from array', function () {
        $request = FactoryAgentRequest::fromArray([
            'factoryRunId' => 'run-1',
            'opts' => ['label' => 'foo'],
            'prompt' => 'Do something',
        ]);

        expect($request->factoryRunId)->toBe('run-1')
            ->and($request->opts)->toBeInstanceOf(FactoryAgentOptions::class)
            ->and($request->opts->label)->toBe('foo')
            ->and($request->prompt)->toBe('Do something');
    });

    it('converts to array correctly', function () {
        $request = new FactoryAgentRequest(
            factoryRunId: 'run-1',
            opts: new FactoryAgentOptions(label: 'foo'),
            prompt: 'Do something',
        );

        expect($request->toArray())->toBe([
            'factoryRunId' => 'run-1',
            'opts' => ['label' => 'foo'],
            'prompt' => 'Do something',
        ]);
    });
});
