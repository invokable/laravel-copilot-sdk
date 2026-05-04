<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ConnectRequest;
use Revolution\Copilot\Types\Rpc\ConnectResult;

describe('ConnectRequest', function () {
    it('can be created from array with token', function () {
        $request = ConnectRequest::fromArray(['token' => 'my-secret-token']);

        expect($request->token)->toBe('my-secret-token');
    });

    it('can be created from empty array', function () {
        $request = ConnectRequest::fromArray([]);

        expect($request->token)->toBeNull();
    });

    it('filters null values in toArray', function () {
        $request = new ConnectRequest;

        expect($request->toArray())->not->toHaveKey('token');
    });

    it('includes token in toArray when set', function () {
        $request = new ConnectRequest(token: 'abc');

        expect($request->toArray())->toHaveKey('token', 'abc');
    });
});

describe('ConnectResult', function () {
    it('can be created from array', function () {
        $result = ConnectResult::fromArray([
            'ok' => true,
            'protocolVersion' => 3,
            'version' => '1.0.0-beta.1',
        ]);

        expect($result->ok)->toBeTrue()
            ->and($result->protocolVersion)->toBe(3)
            ->and($result->version)->toBe('1.0.0-beta.1');
    });

    it('can be created from empty array with defaults', function () {
        $result = ConnectResult::fromArray([]);

        expect($result->ok)->toBeFalse()
            ->and($result->protocolVersion)->toBe(0)
            ->and($result->version)->toBe('');
    });

    it('converts to array', function () {
        $result = new ConnectResult(ok: true, protocolVersion: 3, version: '1.0.0');

        expect($result->toArray())->toBe([
            'ok' => true,
            'protocolVersion' => 3,
            'version' => '1.0.0',
        ]);
    });
});
