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
            ->and($begun->step->result)->toBeNull()
            ->and($begun->step->userCode)->toBeNull()
            ->and($begun->step->verificationUri)->toBeNull()
            ->and($begun->step->expiresIn)->toBeNull()
            ->and($begun->step->interval)->toBeNull();
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

    it('reads and serializes a device-code login step', function () {
        $step = AuthLoginStep::fromArray([
            'kind' => 'device-code',
            'userCode' => 'ABCD-EFGH',
            'verificationUri' => 'https://github.com/login/device',
            'expiresIn' => 900,
            'interval' => 5,
        ]);

        expect($step->kind)->toBe(AuthLoginStepKind::DEVICE_CODE)
            ->and($step->userCode)->toBe('ABCD-EFGH')
            ->and($step->verificationUri)->toBe('https://github.com/login/device')
            ->and($step->expiresIn)->toBe(900)
            ->and($step->interval)->toBe(5)
            ->and($step->toArray())->toBe([
                'kind' => 'device-code',
                'userCode' => 'ABCD-EFGH',
                'verificationUri' => 'https://github.com/login/device',
                'expiresIn' => 900,
                'interval' => 5,
            ]);
    });
});
