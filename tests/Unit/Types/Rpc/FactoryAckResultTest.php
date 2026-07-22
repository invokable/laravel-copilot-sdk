<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryAckResult;

describe('FactoryAckResult', function () {
    it('can be created from array', function () {
        $result = FactoryAckResult::fromArray([]);

        expect($result)->toBeInstanceOf(FactoryAckResult::class);
    });

    it('converts to array correctly', function () {
        $result = new FactoryAckResult;

        expect($result->toArray())->toBe([]);
    });
});
