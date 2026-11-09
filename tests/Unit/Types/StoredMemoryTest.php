<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\StoredMemory;

describe('StoredMemory', function () {
    it('round-trips the memory fields and implements Arrayable', function () {
        $memory = [
            'sessionId' => 'session-1',
            'subject' => 'Project conventions',
            'fact' => 'Use readonly data types.',
            'citations' => 'src/Types/SessionEvent.php:55',
            'reason' => 'Preserves immutable payload behavior.',
        ];

        $storedMemory = StoredMemory::fromArray($memory);

        expect($storedMemory)->toBeInstanceOf(Arrayable::class)
            ->and($storedMemory->toArray())->toBe($memory);
    });
});
