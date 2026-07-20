<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ExpConfigEntry;

describe('ExpConfigEntry', function () {
    it('can be created with id only', function () {
        $entry = new ExpConfigEntry(id: 'entry-1');

        expect($entry->id)->toBe('entry-1')
            ->and($entry->parameters)->toBe([]);
    });

    it('can be created with id and parameters', function () {
        $entry = new ExpConfigEntry(id: 'entry-1', parameters: ['flag' => true, 'ratio' => 0.5]);

        expect($entry->id)->toBe('entry-1')
            ->and($entry->parameters)->toBe(['flag' => true, 'ratio' => 0.5]);
    });

    it('can be created from array', function () {
        $entry = ExpConfigEntry::fromArray([
            'Id' => 'entry-1',
            'Parameters' => ['flag' => true],
        ]);

        expect($entry->id)->toBe('entry-1')
            ->and($entry->parameters)->toBe(['flag' => true]);
    });

    it('converts to array', function () {
        $entry = new ExpConfigEntry(id: 'entry-1', parameters: ['flag' => true]);

        expect($entry->toArray())->toBe([
            'Id' => 'entry-1',
            'Parameters' => ['flag' => true],
        ]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'Id' => 'entry-1',
            'Parameters' => ['flag' => true, 'name' => 'value'],
        ];

        $entry = ExpConfigEntry::fromArray($data);

        expect($entry->toArray())->toBe($data);
    });
});
