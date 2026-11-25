<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\PermissionsExtensionApproval;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsGrantParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsGrantResult;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsListParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsListResult;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsRevokeParams;
use Revolution\Copilot\Types\Rpc\PermissionsExtensionApprovalsRevokeResult;

describe('extension approval DTOs', function () {
    it('round trips required and optional request fields', function () {
        $grant = ['sessionId' => 'session-1', 'requestId' => 'request-1'];
        $list = ['sessionId' => 'session-1'];
        $revoke = ['extensionName' => 'example', 'installationKey' => 'install-1', 'sessionId' => 'session-1'];

        expect(PermissionsExtensionApprovalsGrantParams::fromArray($grant)->toArray())->toBe($grant)
            ->and(PermissionsExtensionApprovalsListParams::fromArray($list)->toArray())->toBe($list)
            ->and(PermissionsExtensionApprovalsRevokeParams::fromArray($revoke)->toArray())->toBe($revoke)
            ->and(PermissionsExtensionApprovalsListParams::fromArray(['sessionId' => null])->toArray())->toBe([])
            ->and(PermissionsExtensionApprovalsRevokeParams::fromArray([
                'extensionName' => 'example',
                'installationKey' => 'install-1',
                'sessionId' => null,
            ])->toArray())->toBe(['extensionName' => 'example', 'installationKey' => 'install-1']);
    });

    it('round trips approvals and typed nested results including false values', function () {
        $approval = [
            'extensionName' => 'example',
            'pluginName' => 'plugin',
            'capabilities' => ['read', 'write'],
            'grantedAt' => '2026-01-01T00:00:00Z',
            'installationKey' => 'install-1',
            'installationLabel' => 'Example',
        ];
        $grant = PermissionsExtensionApprovalsGrantResult::fromArray(['granted' => true, 'approval' => $approval]);
        $list = PermissionsExtensionApprovalsListResult::fromArray(['approvals' => [$approval]]);

        expect(PermissionsExtensionApproval::fromArray($approval)->toArray())->toBe($approval)
            ->and($grant->approval)->toBeInstanceOf(PermissionsExtensionApproval::class)
            ->and($grant->toArray())->toBe(['granted' => true, 'approval' => $approval])
            ->and($list->approvals[0])->toBeInstanceOf(PermissionsExtensionApproval::class)
            ->and($list->toArray())->toBe(['approvals' => [$approval]])
            ->and(PermissionsExtensionApprovalsRevokeResult::fromArray(['revoked' => false])->toArray())->toBe(['revoked' => false]);
    });

    it('omits absent optional approval and installation label fields', function () {
        $approval = [
            'extensionName' => 'example',
            'pluginName' => 'plugin',
            'capabilities' => [],
            'grantedAt' => '2026-01-01T00:00:00Z',
            'installationKey' => 'install-1',
        ];

        expect(PermissionsExtensionApproval::fromArray($approval)->toArray())->toBe($approval)
            ->and(PermissionsExtensionApprovalsGrantResult::fromArray(['granted' => false, 'approval' => null])->toArray())->toBe(['granted' => false])
            ->and(PermissionsExtensionApprovalsListResult::fromArray(['approvals' => []])->toArray())->toBe(['approvals' => []]);
    });
});
