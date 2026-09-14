<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryPauseCheckpointAction;
use Revolution\Copilot\Types\Rpc\FactoryPauseCheckpointRequest;
use Revolution\Copilot\Types\Rpc\SessionFactoryPauseAtCheckpointResult;

describe('FactoryPauseCheckpointAction', function () {
    it('has continue and pause cases', function () {
        expect(FactoryPauseCheckpointAction::CONTINUE->value)->toBe('continue')
            ->and(FactoryPauseCheckpointAction::PAUSE->value)->toBe('pause');
    });
});

describe('FactoryPauseCheckpointRequest', function () {
    it('can be created from array', function () {
        $request = FactoryPauseCheckpointRequest::fromArray([
            'executionToken' => 'token-1',
            'key' => 'checkpoint-1',
            'runId' => 'run-1',
        ]);

        expect($request->executionToken)->toBe('token-1')
            ->and($request->key)->toBe('checkpoint-1')
            ->and($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryPauseCheckpointRequest(
            executionToken: 'token-1',
            key: 'checkpoint-1',
            runId: 'run-1',
        );

        expect($request->toArray())->toBe([
            'executionToken' => 'token-1',
            'key' => 'checkpoint-1',
            'runId' => 'run-1',
        ]);
    });
});

describe('SessionFactoryPauseAtCheckpointResult', function () {
    it('can be created from array', function () {
        $result = SessionFactoryPauseAtCheckpointResult::fromArray(['action' => 'continue']);

        expect($result->action)->toBe(FactoryPauseCheckpointAction::CONTINUE);
    });

    it('converts to array correctly', function () {
        $result = new SessionFactoryPauseAtCheckpointResult(action: FactoryPauseCheckpointAction::PAUSE);

        expect($result->toArray())->toBe(['action' => 'pause']);
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = ['action' => 'pause'];

        expect(SessionFactoryPauseAtCheckpointResult::fromArray($data)->toArray())->toBe($data);
    });
});
