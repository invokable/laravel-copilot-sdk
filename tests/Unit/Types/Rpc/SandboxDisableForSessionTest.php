<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionDecisionOutcome;
use Revolution\Copilot\Enums\PermissionDecisionSource;
use Revolution\Copilot\Enums\PermissionDecisionSurface;
use Revolution\Copilot\Types\Rpc\PermissionDecisionContext;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionRequest;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionResult;

describe('SandboxDisableForSessionRequest', function () {
    it('can be created with required fields only', function () {
        $request = new SandboxDisableForSessionRequest(requestId: 'req-1');

        expect($request->requestId)->toBe('req-1')
            ->and($request->decisionContext)->toBeNull();
    });

    it('can be created from array', function () {
        $request = SandboxDisableForSessionRequest::fromArray([
            'requestId' => 'req-1',
        ]);

        expect($request->requestId)->toBe('req-1');
    });

    it('serializes to array omitting null values', function () {
        $request = new SandboxDisableForSessionRequest(requestId: 'req-1');

        expect($request->toArray())->toBe(['requestId' => 'req-1']);
    });

    it('roundtrips through fromArray/toArray with decision context', function () {
        $data = [
            'requestId' => 'req-1',
            'decisionContext' => [
                'outcome' => PermissionDecisionOutcome::cases()[0]->value,
                'source' => PermissionDecisionSource::cases()[0]->value,
                'surface' => PermissionDecisionSurface::cases()[0]->value,
            ],
        ];

        $request = SandboxDisableForSessionRequest::fromArray($data);

        expect($request->requestId)->toBe('req-1')
            ->and($request->decisionContext)->toBeInstanceOf(PermissionDecisionContext::class)
            ->and($request->toArray())->toBe($data);
    });
});

describe('SandboxDisableForSessionResult', function () {
    it('can be created with all fields', function () {
        $result = new SandboxDisableForSessionResult(success: true, enabled: false);

        expect($result->success)->toBeTrue()
            ->and($result->enabled)->toBeFalse();
    });

    it('can be created from array', function () {
        $result = SandboxDisableForSessionResult::fromArray([
            'success' => true,
            'enabled' => false,
        ]);

        expect($result->success)->toBeTrue()
            ->and($result->enabled)->toBeFalse();
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = ['success' => true, 'enabled' => false];

        expect(SandboxDisableForSessionResult::fromArray($data)->toArray())->toBe($data);
    });
});
