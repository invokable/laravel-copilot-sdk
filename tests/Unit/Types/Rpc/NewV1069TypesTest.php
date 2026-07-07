<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionsAllowAllMode;
use Revolution\Copilot\Enums\PermissionsSetApproveAllSource;
use Revolution\Copilot\Types\Rpc\AllowAllPermissionSetResult;
use Revolution\Copilot\Types\Rpc\AllowAllPermissionState;
use Revolution\Copilot\Types\Rpc\ConnectRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetAllowAllRequest;

describe('PermissionsAllowAllMode', function () {
    it('has the expected cases', function () {
        expect(PermissionsAllowAllMode::Auto->value)->toBe('auto')
            ->and(PermissionsAllowAllMode::Off->value)->toBe('off')
            ->and(PermissionsAllowAllMode::On->value)->toBe('on');
    });

    it('can be resolved from string value', function () {
        expect(PermissionsAllowAllMode::from('auto'))->toBe(PermissionsAllowAllMode::Auto)
            ->and(PermissionsAllowAllMode::from('off'))->toBe(PermissionsAllowAllMode::Off)
            ->and(PermissionsAllowAllMode::from('on'))->toBe(PermissionsAllowAllMode::On);
    });

    it('returns null for unknown values with tryFrom', function () {
        expect(PermissionsAllowAllMode::tryFrom('unknown'))->toBeNull();
    });
});

describe('AllowAllPermissionSetResult', function () {
    it('can be created with required fields', function () {
        $result = new AllowAllPermissionSetResult(enabled: true, success: true);

        expect($result->enabled)->toBeTrue()
            ->and($result->success)->toBeTrue()
            ->and($result->mode)->toBeNull();
    });

    it('can be created with mode', function () {
        $result = new AllowAllPermissionSetResult(enabled: true, success: true, mode: PermissionsAllowAllMode::On);

        expect($result->mode)->toBe(PermissionsAllowAllMode::On);
    });

    it('can be created from array', function () {
        $result = AllowAllPermissionSetResult::fromArray([
            'enabled' => true,
            'success' => true,
            'mode' => 'auto',
        ]);

        expect($result->enabled)->toBeTrue()
            ->and($result->success)->toBeTrue()
            ->and($result->mode)->toBe(PermissionsAllowAllMode::Auto);
    });

    it('can be created from array without mode', function () {
        $result = AllowAllPermissionSetResult::fromArray(['enabled' => false, 'success' => true]);

        expect($result->enabled)->toBeFalse()
            ->and($result->mode)->toBeNull();
    });

    it('converts to array excluding null mode', function () {
        $result = new AllowAllPermissionSetResult(enabled: true, success: true);

        expect($result->toArray())->toBe(['enabled' => true, 'success' => true])
            ->and($result->toArray())->not->toHaveKey('mode');
    });

    it('converts mode enum to string in toArray', function () {
        $result = new AllowAllPermissionSetResult(enabled: true, success: true, mode: PermissionsAllowAllMode::Off);

        expect($result->toArray()['mode'])->toBe('off');
    });
});

describe('AllowAllPermissionState', function () {
    it('can be created with enabled only', function () {
        $state = new AllowAllPermissionState(enabled: false);

        expect($state->enabled)->toBeFalse()
            ->and($state->mode)->toBeNull();
    });

    it('can be created with mode', function () {
        $state = new AllowAllPermissionState(enabled: true, mode: PermissionsAllowAllMode::Auto);

        expect($state->enabled)->toBeTrue()
            ->and($state->mode)->toBe(PermissionsAllowAllMode::Auto);
    });

    it('can be created from array', function () {
        $state = AllowAllPermissionState::fromArray(['enabled' => true, 'mode' => 'on']);

        expect($state->enabled)->toBeTrue()
            ->and($state->mode)->toBe(PermissionsAllowAllMode::On);
    });

    it('converts to array excluding null mode', function () {
        $state = new AllowAllPermissionState(enabled: true);

        expect($state->toArray())->toBe(['enabled' => true])
            ->and($state->toArray())->not->toHaveKey('mode');
    });

    it('converts mode enum to string in toArray', function () {
        $state = new AllowAllPermissionState(enabled: false, mode: PermissionsAllowAllMode::Off);

        expect($state->toArray())->toBe(['enabled' => false, 'mode' => 'off']);
    });
});

describe('PermissionsSetAllowAllRequest', function () {
    it('can be created with no args', function () {
        $request = new PermissionsSetAllowAllRequest;

        expect($request->enabled)->toBeNull()
            ->and($request->mode)->toBeNull()
            ->and($request->model)->toBeNull()
            ->and($request->source)->toBeNull();
    });

    it('can be created with mode', function () {
        $request = new PermissionsSetAllowAllRequest(mode: PermissionsAllowAllMode::Auto, model: 'gpt-4');

        expect($request->mode)->toBe(PermissionsAllowAllMode::Auto)
            ->and($request->model)->toBe('gpt-4');
    });

    it('can be created from array', function () {
        $request = PermissionsSetAllowAllRequest::fromArray([
            'enabled' => true,
            'mode' => 'auto',
            'model' => 'claude-3',
            'source' => 'rpc',
        ]);

        expect($request->enabled)->toBeTrue()
            ->and($request->mode)->toBe(PermissionsAllowAllMode::Auto)
            ->and($request->model)->toBe('claude-3')
            ->and($request->source)->toBe(PermissionsSetApproveAllSource::RPC);
    });

    it('converts to array excluding nulls', function () {
        $request = new PermissionsSetAllowAllRequest(mode: PermissionsAllowAllMode::On);

        expect($request->toArray())->toBe(['mode' => 'on'])
            ->and($request->toArray())->not->toHaveKey('enabled')
            ->and($request->toArray())->not->toHaveKey('model')
            ->and($request->toArray())->not->toHaveKey('source');
    });

    it('converts all fields to array', function () {
        $request = new PermissionsSetAllowAllRequest(
            enabled: false,
            mode: PermissionsAllowAllMode::Off,
            model: 'gpt-4',
            source: PermissionsSetApproveAllSource::SLASH_COMMAND,
        );

        expect($request->toArray())->toBe([
            'enabled' => false,
            'mode' => 'off',
            'model' => 'gpt-4',
            'source' => 'slash_command',
        ]);
    });
});

describe('ConnectRequest with telemetry forwarding', function () {
    it('can be created with enableGitHubTelemetryForwarding', function () {
        $request = new ConnectRequest(enableGitHubTelemetryForwarding: true, token: 'tok-123');

        expect($request->enableGitHubTelemetryForwarding)->toBeTrue()
            ->and($request->token)->toBe('tok-123');
    });

    it('can be created from array with new field', function () {
        $request = ConnectRequest::fromArray([
            'enableGitHubTelemetryForwarding' => true,
            'token' => 'conn-token',
        ]);

        expect($request->enableGitHubTelemetryForwarding)->toBeTrue()
            ->and($request->token)->toBe('conn-token');
    });

    it('excludes null fields from toArray', function () {
        $request = new ConnectRequest;

        expect($request->toArray())->toBe([]);
    });

    it('includes enableGitHubTelemetryForwarding in toArray when set', function () {
        $request = new ConnectRequest(enableGitHubTelemetryForwarding: true);

        expect($request->toArray())->toBe(['enableGitHubTelemetryForwarding' => true]);
    });
});
