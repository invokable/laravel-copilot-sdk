<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AuthLoginCancelRequest;
use Revolution\Copilot\Types\Rpc\AuthWrite;
use Revolution\Copilot\Types\Rpc\AuthWriteResult;

describe('AuthWrite', function () {
    it('converts to array with all fields', function () {
        $write = new AuthWrite(kind: 'token', selectionId: 's1', host: 'github.com', login: 'octocat', token: 'abc');

        expect($write->toArray())->toBe([
            'kind' => 'token',
            'selectionId' => 's1',
            'host' => 'github.com',
            'login' => 'octocat',
            'token' => 'abc',
        ]);
    });

    it('omits null fields', function () {
        $write = new AuthWrite(kind: 'logout');

        expect($write->toArray())->toBe(['kind' => 'logout']);
    });
});

describe('AuthWriteResult', function () {
    it('can be created from array', function () {
        $result = AuthWriteResult::fromArray(['ok' => true, 'moreUsers' => false]);

        expect($result->ok)->toBeTrue()
            ->and($result->moreUsers)->toBeFalse()
            ->and($result->toArray())->toBe(['ok' => true, 'moreUsers' => false]);
    });

    it('defaults when empty', function () {
        $result = AuthWriteResult::fromArray([]);

        expect($result->ok)->toBeFalse()
            ->and($result->moreUsers)->toBeNull()
            ->and($result->toArray())->toBe(['ok' => false]);
    });
});

describe('AuthLoginCancelRequest', function () {
    it('converts to array', function () {
        expect((new AuthLoginCancelRequest(flowId: 'flow-1'))->toArray())->toBe(['flowId' => 'flow-1']);
    });
});
