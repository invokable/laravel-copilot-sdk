<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryJournalGetResult;

describe('FactoryJournalGetResult', function () {
    it('can be created from array', function () {
        $result = FactoryJournalGetResult::fromArray([
            'hit' => true,
            'resultJson' => ['foo' => 'bar'],
        ]);

        expect($result->hit)->toBeTrue()
            ->and($result->resultJson)->toBe(['foo' => 'bar']);
    });

    it('defaults resultJson to null', function () {
        $result = FactoryJournalGetResult::fromArray(['hit' => false]);

        expect($result->hit)->toBeFalse()
            ->and($result->resultJson)->toBeNull();
    });

    it('converts to array correctly', function () {
        $result = new FactoryJournalGetResult(hit: true, resultJson: 'value');

        expect($result->toArray())->toBe([
            'hit' => true,
            'resultJson' => 'value',
        ]);
    });
});
