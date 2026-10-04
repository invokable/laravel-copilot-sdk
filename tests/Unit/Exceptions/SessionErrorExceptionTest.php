<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\SessionErrorException;

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
