<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\SessionErrorException;
use Revolution\Copilot\Exceptions\SessionTimeoutException;
use Revolution\Copilot\Exceptions\StrayRequestException;

describe('JsonRpcException', function () {
    it('formats message with code and description', function () {
        $e = new JsonRpcException(-32601, 'Method not found');

        expect($e->getMessage())->toBe('JSON-RPC Error -32601: Method not found')
            ->and($e->code)->toBe(-32601)
            ->and($e->data)->toBeNull();
    });

    it('stores optional data', function () {
        $e = new JsonRpcException(-32602, 'Invalid params', ['field' => 'sessionId']);

        expect($e->data)->toBe(['field' => 'sessionId']);
    });

    it('is an instance of Exception', function () {
        $e = new JsonRpcException(0, 'error');

        expect($e)->toBeInstanceOf(Exception::class);
    });
});

describe('SessionErrorException', function () {
    it('formats message with session error prefix', function () {
        $e = new SessionErrorException('session not found');

        expect($e->getMessage())->toBe('Session error: session not found');
    });

    it('is an instance of RuntimeException', function () {
        $e = new SessionErrorException('something went wrong');

        expect($e)->toBeInstanceOf(RuntimeException::class);
    });
});

describe('SessionTimeoutException', function () {
    it('formats message with timeout seconds', function () {
        $e = new SessionTimeoutException(30.0);

        expect($e->getMessage())->toBe('Timeout after 30s waiting for session.idle');
    });

    it('formats fractional timeout', function () {
        $e = new SessionTimeoutException(2.5);

        expect($e->getMessage())->toBe('Timeout after 2.5s waiting for session.idle');
    });

    it('is an instance of RuntimeException', function () {
        $e = new SessionTimeoutException(10.0);

        expect($e)->toBeInstanceOf(RuntimeException::class);
    });
});

describe('StrayRequestException', function () {
    it('formats message with method name', function () {
        $e = new StrayRequestException('session.create');

        expect($e->getMessage())->toBe('Attempted request to [session.create] without a matching fake.');
    });

    it('is an instance of RuntimeException', function () {
        $e = new StrayRequestException('ping');

        expect($e)->toBeInstanceOf(RuntimeException::class);
    });
});
