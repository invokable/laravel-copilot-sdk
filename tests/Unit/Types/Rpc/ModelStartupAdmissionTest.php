<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelStartupAdmissionRequest;
use Revolution\Copilot\Types\Rpc\ModelStartupAdmissionResult;

describe('ModelStartupAdmissionRequest', function () {
    it('round trips optional claim values', function ($data) {
        expect(ModelStartupAdmissionRequest::fromArray($data)->toArray())->toBe($data);
    })->with([[[]], [['claimEnablement' => true]], [['claimEnablement' => false]]]);
});

describe('ModelStartupAdmissionResult', function () {
    it('round trips model admission state', function ($data) {
        expect(ModelStartupAdmissionResult::fromArray($data)->toArray())->toBe($data);
    })->with([
        [[
            'selectedModel' => 'model-a',
            'catalogReady' => true,
            'requiresEnablement' => true,
            'claimedEnablement' => true,
            'modelToEnable' => 'model-a',
        ]],
        [[
            'catalogReady' => false,
            'requiresEnablement' => false,
            'claimedEnablement' => false,
        ]],
    ]);

    it('defaults missing state and omits optional models', function () {
        expect(ModelStartupAdmissionResult::fromArray([])->toArray())->toBe([
            'catalogReady' => false,
            'requiresEnablement' => false,
            'claimedEnablement' => false,
        ]);
    });
});
