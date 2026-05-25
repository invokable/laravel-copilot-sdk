<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CanvasInvokeActionResult;

describe('CanvasInvokeActionResult', function () {
    it('can be created from array with result', function () {
        $result = CanvasInvokeActionResult::fromArray([
            'result' => ['status' => 'success'],
        ]);

        expect($result->result)->toBe(['status' => 'success']);
    });

    it('can be created from array without result', function () {
        $result = CanvasInvokeActionResult::fromArray([]);

        expect($result->result)->toBeNull();
    });

    it('converts to array with result', function () {
        $result = new CanvasInvokeActionResult(result: ['data' => 'test']);

        expect($result->toArray())->toBe([
            'result' => ['data' => 'test'],
        ]);
    });

    it('excludes null result from array', function () {
        $result = new CanvasInvokeActionResult;

        expect($result->toArray())->toBe([]);
    });
});
