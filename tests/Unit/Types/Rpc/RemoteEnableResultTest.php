<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\RemoteEnableResult;

describe('RemoteEnableResult', function () {
    it('can be created from array with all fields', function () {
        $result = RemoteEnableResult::fromArray([
            'remoteSteerable' => true,
            'url' => 'https://github.com/mission-control/session/123',
        ]);

        expect($result->remoteSteerable)->toBeTrue()
            ->and($result->url)->toBe('https://github.com/mission-control/session/123');
    });

    it('can be created from array with minimal fields', function () {
        $result = RemoteEnableResult::fromArray([
            'remoteSteerable' => false,
        ]);

        expect($result->remoteSteerable)->toBeFalse()
            ->and($result->url)->toBeNull();
    });

    it('handles empty array with defaults', function () {
        $result = RemoteEnableResult::fromArray([]);

        expect($result->remoteSteerable)->toBeFalse()
            ->and($result->url)->toBeNull();
    });

    it('converts to array with url', function () {
        $result = RemoteEnableResult::fromArray([
            'remoteSteerable' => true,
            'url' => 'https://example.com',
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('remoteSteerable', true)
            ->and($array)->toHaveKey('url', 'https://example.com');
    });

    it('converts to array without null url', function () {
        $result = RemoteEnableResult::fromArray([
            'remoteSteerable' => false,
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('remoteSteerable', false)
            ->and($array)->not->toHaveKey('url');
    });
});
