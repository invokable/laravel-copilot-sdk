<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\SessionTimeoutException;

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
