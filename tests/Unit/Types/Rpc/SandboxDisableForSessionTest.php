<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionDecisionOutcome;
use Revolution\Copilot\Enums\PermissionDecisionSource;
use Revolution\Copilot\Enums\PermissionDecisionSurface;
use Revolution\Copilot\Enums\SandboxConfigSource;
use Revolution\Copilot\Types\Rpc\PermissionDecisionContext;
use Revolution\Copilot\Types\Rpc\SandboxConfig;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionRequest;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionResult;
use Revolution\Copilot\Types\Rpc\SessionSandboxGetStateResult;
use Revolution\Copilot\Types\Rpc\SessionSandboxResetSessionChoicesRequest;
use Revolution\Copilot\Types\Rpc\SessionSandboxResetSessionChoicesResult;

describe('SessionSandboxResetSessionChoicesRequest', function () {
    it('round-trips JSON preferences without discarding unknown fields', function () {
        $data = ['config' => ['enabled' => true, 'future' => ['value' => false]], 'configSource' => 'user_enabled'];
        $request = SessionSandboxResetSessionChoicesRequest::fromArray($data);

        expect($request->configSource)->toBe(SandboxConfigSource::USER_ENABLED)
            ->and($request->toArray())->toBe($data);
    });

    it('serializes a typed config and preserves null and unknown sources', function () {
        $typed = new SessionSandboxResetSessionChoicesRequest(new SandboxConfig(enabled: false), SandboxConfigSource::USER_DISABLED);
        $unknown = SessionSandboxResetSessionChoicesRequest::fromArray(['config' => null, 'configSource' => 'future_source']);

        expect($typed->toArray())->toBe(['config' => ['enabled' => false], 'configSource' => 'user_disabled'])
            ->and($unknown->toArray())->toBe(['config' => null, 'configSource' => 'future_source']);
    });
});

describe('session sandbox state results', function () {
    it('round-trips effective state and nullable or explicit session choices', function (string $class, array $data) {
        $result = $class::fromArray($data);

        expect($result->toArray())->toBe($data)
            ->and($result->configSource)->toBe(SandboxConfigSource::USER_ENABLED);
    })->with([
        'get state with null values' => [SessionSandboxGetStateResult::class, [
            'incarnation' => 1,
            'revision' => 0,
            'config' => null,
            'configSource' => 'user_enabled',
            'disabledForSession' => false,
            'autoApproveBypass' => false,
            'sessionOverride' => null,
        ]],
        'reset with explicit choice' => [SessionSandboxResetSessionChoicesResult::class, [
            'incarnation' => 2,
            'revision' => 3,
            'config' => ['enabled' => false, 'future' => 'value'],
            'configSource' => 'user_enabled',
            'disabledForSession' => true,
            'autoApproveBypass' => true,
            'sessionOverride' => true,
        ]],
    ]);
});

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
