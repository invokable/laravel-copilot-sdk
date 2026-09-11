<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryPauseInfo;
use Revolution\Copilot\Types\Rpc\FactoryPauseRequest;

describe('FactoryPauseRequest', function () {
    it('can be created from array', function () {
        $request = FactoryPauseRequest::fromArray(['runId' => 'run-1']);

        expect($request->runId)->toBe('run-1');
    });

    it('converts to array correctly', function () {
        $request = new FactoryPauseRequest(runId: 'run-1');

        expect($request->toArray())->toBe(['runId' => 'run-1']);
    });
});

describe('FactoryPauseInfo', function () {
    it('can be created for a user-initiated pause', function () {
        $info = new FactoryPauseInfo(type: 'user');

        expect($info->type)->toBe('user')
            ->and($info->key)->toBeNull();
    });

    it('can be created for a checkpoint-initiated pause', function () {
        $info = new FactoryPauseInfo(type: 'checkpoint', key: 'checkpoint-1');

        expect($info->type)->toBe('checkpoint')
            ->and($info->key)->toBe('checkpoint-1');
    });

    it('can be created from array', function () {
        $info = FactoryPauseInfo::fromArray(['type' => 'checkpoint', 'key' => 'checkpoint-1']);

        expect($info->type)->toBe('checkpoint')
            ->and($info->key)->toBe('checkpoint-1');
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = ['type' => 'checkpoint', 'key' => 'checkpoint-1'];

        expect(FactoryPauseInfo::fromArray($data)->toArray())->toBe($data);
    });

    it('omits key when null', function () {
        $info = new FactoryPauseInfo(type: 'user');

        expect($info->toArray())->toBe(['type' => 'user']);
    });
});
