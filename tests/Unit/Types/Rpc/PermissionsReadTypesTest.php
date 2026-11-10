<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\PermissionsAdditionalDirectoriesListResult;
use Revolution\Copilot\Types\Rpc\PermissionsAllowAllBaseline;
use Revolution\Copilot\Types\Rpc\SessionPermissionsGetAllowAllResult;
use Revolution\Copilot\Types\Rpc\SessionPermissionsManagedReviewRequiredRequest;

describe('permission read RPC types', function () {
    it('round trips allow-all state with its launch baseline', function () {
        $data = [
            'active' => true,
            'autoApproval' => false,
            'storedAutoApproval' => true,
            'runtimeOverride' => false,
            'baseline' => ['tools' => true, 'paths' => false, 'urls' => true],
        ];

        $result = SessionPermissionsGetAllowAllResult::fromArray($data);

        expect($result->baseline)->toBeInstanceOf(PermissionsAllowAllBaseline::class)
            ->and($result->toArray())->toBe($data);
    });

    it('round trips a managed review permission request', function () {
        $data = ['request' => ['kind' => 'tool', 'toolName' => 'bash']];

        expect(SessionPermissionsManagedReviewRequiredRequest::fromArray($data)->toArray())->toBe($data);
    });

    it('round trips additional model-context directories', function () {
        $data = ['directories' => ['/workspace/shared', '/workspace/docs']];

        expect(PermissionsAdditionalDirectoriesListResult::fromArray($data)->toArray())->toBe($data);
    });

    it('defaults an absent additional-directory list to an empty list', function () {
        expect(PermissionsAdditionalDirectoriesListResult::fromArray([])->directories)->toBe([]);
    });
});
