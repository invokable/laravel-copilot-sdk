<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ForegroundSessionInfo;

describe('ForegroundSessionInfo', function () {
    it('can be created from array with all fields', function () {
        $info = ForegroundSessionInfo::fromArray([
            'sessionId' => 'session-123',
            'workspacePath' => '/path/to/workspace',
        ]);

        expect($info->sessionId)->toBe('session-123')
            ->and($info->workspacePath)->toBe('/path/to/workspace');
    });

    it('can be created from array with no fields', function () {
        $info = ForegroundSessionInfo::fromArray([]);

        expect($info->sessionId)->toBeNull()
            ->and($info->workspacePath)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $info = new ForegroundSessionInfo(
            sessionId: 'session-456',
            workspacePath: '/another/path',
        );

        $array = $info->toArray();

        expect($array['sessionId'])->toBe('session-456')
            ->and($array['workspacePath'])->toBe('/another/path');
    });

    it('filters null values in toArray', function () {
        $info = new ForegroundSessionInfo;

        $array = $info->toArray();

        expect($array)->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $info = new ForegroundSessionInfo;

        expect($info)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
