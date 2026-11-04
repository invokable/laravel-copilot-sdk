<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\LocalSessionMetadataValue;

describe('LocalSessionMetadataValue', function () {
    it('can be created from array with client names', function () {
        $value = LocalSessionMetadataValue::fromArray([
            'sessionId' => 'local-session',
            'startTime' => '2026-10-16T09:00:00Z',
            'modifiedTime' => '2026-10-17T09:00:00Z',
            'clientName' => 'github/cli',
            'lastClientName' => 'laravel',
        ]);

        expect($value->clientName)->toBe('github/cli')
            ->and($value->lastClientName)->toBe('laravel');
    });

    it('defaults lastClientName to null when absent', function () {
        $value = LocalSessionMetadataValue::fromArray([
            'sessionId' => 'local-session',
            'startTime' => '2026-10-16T09:00:00Z',
            'modifiedTime' => '2026-10-17T09:00:00Z',
        ]);

        expect($value->lastClientName)->toBeNull()
            ->and($value->toArray())->not->toHaveKey('lastClientName');
    });

    it('round trips lastClientName through arrays', function () {
        $value = new LocalSessionMetadataValue(
            sessionId: 'local-session',
            startTime: '2026-10-16T09:00:00Z',
            modifiedTime: '2026-10-17T09:00:00Z',
            clientName: 'github/cli',
            lastClientName: 'laravel',
        );

        $array = $value->toArray();

        expect($array['lastClientName'])->toBe('laravel')
            ->and(LocalSessionMetadataValue::fromArray($array))->toEqual($value);
    });
});
