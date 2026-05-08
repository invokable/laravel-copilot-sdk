<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\AutoModeSwitchRequest;

describe('AutoModeSwitchRequest', function () {
    it('can be created from array with all fields', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'errorCode' => 'RATE_LIMIT_EXCEEDED',
            'retryAfterSeconds' => 60,
        ]);

        expect($request->errorCode)->toBe('RATE_LIMIT_EXCEEDED')
            ->and($request->retryAfterSeconds)->toBe(60);
    });

    it('handles missing optional fields', function () {
        $request = AutoModeSwitchRequest::fromArray([]);

        expect($request->errorCode)->toBeNull()
            ->and($request->retryAfterSeconds)->toBeNull();
    });

    it('casts retryAfterSeconds to int', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'retryAfterSeconds' => '30',
        ]);

        expect($request->retryAfterSeconds)->toBe(30);
    });

    it('converts to array filtering null values', function () {
        $request = AutoModeSwitchRequest::fromArray([
            'errorCode' => 'RATE_LIMIT_EXCEEDED',
        ]);

        $array = $request->toArray();

        expect($array)->toHaveKey('errorCode', 'RATE_LIMIT_EXCEEDED')
            ->and($array)->not->toHaveKey('retryAfterSeconds');
    });

    it('implements Arrayable', function () {
        expect(new AutoModeSwitchRequest)->toBeInstanceOf(Arrayable::class);
    });
});
