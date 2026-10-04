<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\StrayRequestException;

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
