<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\InterruptMainTurnRequest;

describe('InterruptMainTurnRequest', function () {
    it('can be created from array with flushQueued', function () {
        $request = InterruptMainTurnRequest::fromArray([
            'flushQueued' => true,
        ]);

        expect($request->flushQueued)->toBeTrue()
            ->and($request->toArray())->toBe(['flushQueued' => true]);
    });

    it('defaults flushQueued to null and omits it from the array', function () {
        $request = InterruptMainTurnRequest::fromArray([]);

        expect($request->flushQueued)->toBeNull()
            ->and($request->toArray())->toBe([]);
    });

    it('preserves false when converting to array', function () {
        $request = InterruptMainTurnRequest::fromArray([
            'flushQueued' => false,
        ]);

        expect($request->flushQueued)->toBeFalse()
            ->and($request->toArray())->toBe(['flushQueued' => false]);
    });
});
