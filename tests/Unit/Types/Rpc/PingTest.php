<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\PingParams;
use Revolution\Copilot\Types\Rpc\PingResult;

describe('PingResult', function () {
    it('can be created from array', function () {
        $result = PingResult::fromArray([
            'message' => 'pong',
            'timestamp' => 1234567890.0,
            'protocolVersion' => 1.0,
        ]);

        expect($result->message)->toBe('pong')
            ->and($result->timestamp)->toBe(1234567890.0)
            ->and($result->protocolVersion)->toBe(1.0);
    });

    it('can convert to array', function () {
        $result = new PingResult(
            message: 'hello',
            timestamp: 1234567890.0,
            protocolVersion: 2.0,
        );

        expect($result->toArray())->toBe([
            'message' => 'hello',
            'timestamp' => 1234567890.0,
            'protocolVersion' => 2.0,
        ]);
    });

    it('implements Arrayable interface', function () {
        $result = new PingResult(message: 'test', timestamp: 0, protocolVersion: 1.0);
        expect($result)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});

describe('PingParams', function () {
    it('can be created from array with message', function () {
        $params = PingParams::fromArray(['message' => 'hello']);

        expect($params->message)->toBe('hello');
    });

    it('can be created from array without message', function () {
        $params = PingParams::fromArray([]);

        expect($params->message)->toBeNull();
    });

    it('filters null values in toArray', function () {
        $params = new PingParams;

        expect($params->toArray())->toBe([]);
    });

    it('includes message in toArray when set', function () {
        $params = new PingParams(message: 'hello');

        expect($params->toArray())->toBe(['message' => 'hello']);
    });
});
