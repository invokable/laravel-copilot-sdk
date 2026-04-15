<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\PingRequest;
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
        expect($result)->toBeInstanceOf(Arrayable::class);
    });
});

describe('PingRequest', function () {
    it('can be created from array with message', function () {
        $params = PingRequest::fromArray(['message' => 'hello']);

        expect($params->message)->toBe('hello');
    });

    it('can be created from array without message', function () {
        $params = PingRequest::fromArray([]);

        expect($params->message)->toBeNull();
    });

    it('filters null values in toArray', function () {
        $params = new PingRequest;

        expect($params->toArray())->toBe([]);
    });

    it('includes message in toArray when set', function () {
        $params = new PingRequest(message: 'hello');

        expect($params->toArray())->toBe(['message' => 'hello']);
    });
});
