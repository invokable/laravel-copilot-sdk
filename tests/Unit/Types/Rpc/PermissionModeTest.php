<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionMode;
use Revolution\Copilot\Enums\PermissionModeSource;
use Revolution\Copilot\Types\Rpc\ConnectRequest;
use Revolution\Copilot\Types\Rpc\PermissionsGetModeResult;
use Revolution\Copilot\Types\Rpc\PermissionsSetModeRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetModeResult;

describe('PermissionMode', function () {
    it('has the expected cases', function () {
        expect(PermissionMode::Manual->value)->toBe('manual')
            ->and(PermissionMode::Assisted->value)->toBe('assisted')
            ->and(PermissionMode::AllowAll->value)->toBe('allow-all');
    });

    it('can be resolved from string value', function () {
        expect(PermissionMode::from('manual'))->toBe(PermissionMode::Manual)
            ->and(PermissionMode::from('assisted'))->toBe(PermissionMode::Assisted)
            ->and(PermissionMode::from('allow-all'))->toBe(PermissionMode::AllowAll);
    });

    it('returns null for unknown values with tryFrom', function () {
        expect(PermissionMode::tryFrom('unknown'))->toBeNull();
    });
});

describe('PermissionModeSource', function () {
    it('has the expected cases', function () {
        expect(PermissionModeSource::CLI_FLAG->value)->toBe('cli_flag')
            ->and(PermissionModeSource::SLASH_COMMAND->value)->toBe('slash_command')
            ->and(PermissionModeSource::AUTOPILOT_CONFIRMATION->value)->toBe('autopilot_confirmation')
            ->and(PermissionModeSource::USER_SETTING->value)->toBe('user_setting')
            ->and(PermissionModeSource::RPC->value)->toBe('rpc');
    });
});

describe('PermissionsSetModeRequest', function () {
    it('can be created with required fields', function () {
        $request = new PermissionsSetModeRequest(mode: PermissionMode::Manual);

        expect($request->mode)->toBe(PermissionMode::Manual)
            ->and($request->assistedApprovalModel)->toBeNull()
            ->and($request->source)->toBeNull();
    });

    it('can be created with assisted mode and model', function () {
        $request = new PermissionsSetModeRequest(mode: PermissionMode::Assisted, assistedApprovalModel: 'gpt-5.5');

        expect($request->mode)->toBe(PermissionMode::Assisted)
            ->and($request->assistedApprovalModel)->toBe('gpt-5.5');
    });

    it('can be created from array', function () {
        $request = PermissionsSetModeRequest::fromArray([
            'mode' => 'allow-all',
            'assistedApprovalModel' => 'claude-3',
            'source' => 'rpc',
        ]);

        expect($request->mode)->toBe(PermissionMode::AllowAll)
            ->and($request->assistedApprovalModel)->toBe('claude-3')
            ->and($request->source)->toBe(PermissionModeSource::RPC);
    });

    it('converts to array excluding nulls', function () {
        $request = new PermissionsSetModeRequest(mode: PermissionMode::AllowAll);

        expect($request->toArray())->toBe(['mode' => 'allow-all'])
            ->and($request->toArray())->not->toHaveKey('assistedApprovalModel')
            ->and($request->toArray())->not->toHaveKey('source');
    });

    it('converts all fields to array', function () {
        $request = new PermissionsSetModeRequest(
            mode: PermissionMode::Assisted,
            assistedApprovalModel: 'gpt-5.5',
            source: PermissionModeSource::SLASH_COMMAND,
        );

        expect($request->toArray())->toBe([
            'mode' => 'assisted',
            'assistedApprovalModel' => 'gpt-5.5',
            'source' => 'slash_command',
        ]);
    });
});

describe('PermissionsSetModeResult', function () {
    it('can be created from array', function () {
        $result = PermissionsSetModeResult::fromArray(['success' => true, 'mode' => 'allow-all']);

        expect($result->success)->toBeTrue()
            ->and($result->mode)->toBe(PermissionMode::AllowAll);
    });

    it('converts to array', function () {
        $result = new PermissionsSetModeResult(success: true, mode: PermissionMode::Manual);

        expect($result->toArray())->toBe(['success' => true, 'mode' => 'manual']);
    });
});

describe('PermissionsGetModeResult', function () {
    it('can be created from array', function () {
        $result = PermissionsGetModeResult::fromArray(['mode' => 'assisted']);

        expect($result->mode)->toBe(PermissionMode::Assisted);
    });

    it('converts to array', function () {
        $result = new PermissionsGetModeResult(mode: PermissionMode::Manual);

        expect($result->toArray())->toBe(['mode' => 'manual']);
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
