<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthLoginStepKind;
use Revolution\Copilot\Types\Rpc\AuthLoginBegun;
use Revolution\Copilot\Types\Rpc\AuthLoginStep;

describe('AuthLoginBegun', function () {
    it('creates a typed login step from the wire payload', function () {
        $begun = AuthLoginBegun::fromArray([
            'flowId' => 'flow-123',
            'step' => [
                'kind' => 'open-url',
                'url' => 'https://github.com/login/device',
            ],
        ]);

        expect($begun->flowId)->toBe('flow-123')
            ->and($begun->step)->toBeInstanceOf(AuthLoginStep::class)
            ->and($begun->step->kind)->toBe(AuthLoginStepKind::OPEN_URL)
            ->and($begun->step->url)->toBe('https://github.com/login/device');
    });

    it('uses empty defaults when the wire payload omits fields', function () {
        $begun = AuthLoginBegun::fromArray([]);

        expect($begun->flowId)->toBe('')
            ->and($begun->step->kind)->toBe('')
            ->and($begun->step->url)->toBeNull()
            ->and($begun->step->prompt)->toBeNull()
            ->and($begun->step->message)->toBeNull()
            ->and($begun->step->result)->toBeNull();
    });

    it('serializes the flow id and nested login step to the wire format', function () {
        $begun = AuthLoginBegun::fromArray([
            'flowId' => 'flow-456',
            'step' => [
                'kind' => 'input-required',
                'prompt' => 'Enter the device code',
            ],
        ]);

        expect($begun->toArray())->toBe([
            'flowId' => 'flow-456',
            'step' => [
                'kind' => 'input-required',
                'prompt' => 'Enter the device code',
            ],
        ]);
    });
});
