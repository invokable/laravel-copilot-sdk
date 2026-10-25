<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ToolsInitializeAndValidateResult;

describe('ToolsInitializeAndValidateResult', function () {
    it('round-trips an empty result', function () {
        $result = ToolsInitializeAndValidateResult::fromArray([]);

        expect($result)->toBeInstanceOf(ToolsInitializeAndValidateResult::class)
            ->and($result->toArray())->toBe([]);
    });
});
