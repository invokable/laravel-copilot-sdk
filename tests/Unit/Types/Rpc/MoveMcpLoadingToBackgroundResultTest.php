<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\MoveMcpLoadingToBackgroundResult;

describe('MoveMcpLoadingToBackgroundResult', function () {
    it('can be created', function () {
        $result = new MoveMcpLoadingToBackgroundResult(movedToBackground: true);

        expect($result->movedToBackground)->toBeTrue();
    });

    it('can be created from array', function () {
        $result = MoveMcpLoadingToBackgroundResult::fromArray(['movedToBackground' => false]);

        expect($result->movedToBackground)->toBeFalse();
    });

    it('converts to array', function () {
        $result = new MoveMcpLoadingToBackgroundResult(movedToBackground: true);

        expect($result->toArray())->toBe(['movedToBackground' => true]);
    });
});
