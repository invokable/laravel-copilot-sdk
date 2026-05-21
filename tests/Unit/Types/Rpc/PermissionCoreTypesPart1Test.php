<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionDecisionKind;
use Revolution\Copilot\Enums\PermissionsConfigureAdditionalContentExclusionPolicyScope;
use Revolution\Copilot\Enums\PermissionsModifyRulesScope;
use Revolution\Copilot\Types\Rpc\PendingPermissionRequest;
use Revolution\Copilot\Types\Rpc\PendingPermissionRequestList;
use Revolution\Copilot\Types\Rpc\PermissionPromptShownNotification;
use Revolution\Copilot\Types\Rpc\PermissionsConfigureResult;
use Revolution\Copilot\Types\Rpc\PermissionsModifyRulesResult;
use Revolution\Copilot\Types\Rpc\PermissionsNotifyPromptShownResult;
use Revolution\Copilot\Types\Rpc\PermissionsPathsAddResult;
use Revolution\Copilot\Types\Rpc\PermissionsPathsListRequest;
use Revolution\Copilot\Types\Rpc\PermissionsPathsUpdatePrimaryResult;
use Revolution\Copilot\Types\Rpc\PermissionsPendingRequestsRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetRequiredRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetRequiredResult;
use Revolution\Copilot\Types\Rpc\PermissionsUrlsSetUnrestrictedModeResult;

describe('permission core enums (part 1)', function () {
    it('exposes permission decision kinds', function () {
        expect(PermissionDecisionKind::APPROVE_ONCE->value)->toBe('approve-once')
            ->and(PermissionDecisionKind::DENIED_BY_PERMISSION_REQUEST_HOOK->value)->toBe('denied-by-permission-request-hook');
    });

    it('exposes permission rule scopes', function () {
        expect(PermissionsModifyRulesScope::SESSION->value)->toBe('session')
            ->and(PermissionsModifyRulesScope::LOCATION->value)->toBe('location');
    });

    it('exposes content exclusion scopes', function () {
        expect(PermissionsConfigureAdditionalContentExclusionPolicyScope::ALL->value)->toBe('all')
            ->and(PermissionsConfigureAdditionalContentExclusionPolicyScope::REPO->value)->toBe('repo');
    });
});

describe('pending permission request core types (part 1)', function () {
    it('maps pending permission request list from and to array', function () {
        $list = PendingPermissionRequestList::fromArray([
            'items' => [
                [
                    'requestId' => 'req-1',
                    'request' => ['kind' => 'tool', 'message' => 'Allow tool use?'],
                ],
            ],
        ]);

        expect($list->items)->toHaveCount(1)
            ->and($list->items[0])->toBeInstanceOf(PendingPermissionRequest::class)
            ->and($list->items[0]->requestId)->toBe('req-1')
            ->and($list->toArray())->toBe([
                'items' => [[
                    'requestId' => 'req-1',
                    'request' => ['kind' => 'tool', 'message' => 'Allow tool use?'],
                ]],
            ]);
    });

    it('maps permission prompt shown payload', function () {
        $notification = PermissionPromptShownNotification::fromArray(['message' => 'Tool permission requested']);

        expect($notification->message)->toBe('Tool permission requested')
            ->and($notification->toArray())->toBe(['message' => 'Tool permission requested']);
    });
});

describe('permission common request/result types (part 1)', function () {
    it('maps empty request payloads', function () {
        expect(PermissionsPendingRequestsRequest::fromArray([])->toArray())->toBe([])
            ->and(PermissionsPathsListRequest::fromArray([])->toArray())->toBe([]);
    });

    it('maps permissions set required request', function () {
        $request = PermissionsSetRequiredRequest::fromArray(['required' => true]);

        expect($request->required)->toBeTrue()
            ->and($request->toArray())->toBe(['required' => true]);
    });

    it('maps success-only permission responses', function () {
        expect(PermissionsConfigureResult::fromArray(['success' => true])->toArray())->toBe(['success' => true])
            ->and(PermissionsModifyRulesResult::fromArray(['success' => true])->toArray())->toBe(['success' => true])
            ->and(PermissionsNotifyPromptShownResult::fromArray(['success' => true])->toArray())->toBe(['success' => true])
            ->and(PermissionsPathsAddResult::fromArray(['success' => true])->toArray())->toBe(['success' => true])
            ->and(PermissionsPathsUpdatePrimaryResult::fromArray(['success' => true])->toArray())->toBe(['success' => true])
            ->and(PermissionsSetRequiredResult::fromArray(['success' => true])->toArray())->toBe(['success' => true])
            ->and(PermissionsUrlsSetUnrestrictedModeResult::fromArray(['success' => true])->toArray())->toBe(['success' => true]);
    });
});
