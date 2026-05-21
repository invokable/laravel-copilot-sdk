<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionsModifyRulesScope;
use Revolution\Copilot\Types\Rpc\PermissionPathsAddParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsAllowedCheckParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsAllowedCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionPathsList;
use Revolution\Copilot\Types\Rpc\PermissionPathsUpdatePrimaryParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsWorkspaceCheckParams;
use Revolution\Copilot\Types\Rpc\PermissionPathsWorkspaceCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionsConfigureParams;
use Revolution\Copilot\Types\Rpc\PermissionsModifyRulesParams;
use Revolution\Copilot\Types\Rpc\PermissionUrlsSetUnrestrictedModeParams;

describe('permission remaining types (part 2)', function () {
    it('maps permissions configure params', function () {
        $params = PermissionsConfigureParams::fromArray([
            'approveAllReadPermissionRequests' => true,
            'approveAllToolPermissionRequests' => false,
            'paths' => ['primary' => '/workspace'],
            'rules' => ['approved' => [], 'denied' => []],
            'urls' => ['unrestricted' => true],
        ]);

        expect($params->toArray())->toBe([
            'approveAllReadPermissionRequests' => true,
            'approveAllToolPermissionRequests' => false,
            'paths' => ['primary' => '/workspace'],
            'rules' => ['approved' => [], 'denied' => []],
            'urls' => ['unrestricted' => true],
        ]);
    });

    it('maps permissions modify rules params with enum scope', function () {
        $params = new PermissionsModifyRulesParams(
            scope: PermissionsModifyRulesScope::LOCATION,
            add: [['tool' => 'bash']],
            removeAll: true,
        );

        expect($params->toArray())->toBe([
            'scope' => 'location',
            'add' => [['tool' => 'bash']],
            'removeAll' => true,
        ]);
    });

    it('maps permission paths params/results and urls params', function () {
        $add = PermissionPathsAddParams::fromArray(['path' => '/workspace']);
        $allowedParams = PermissionPathsAllowedCheckParams::fromArray(['path' => '/workspace/file.txt']);
        $allowedResult = PermissionPathsAllowedCheckResult::fromArray(['allowed' => true]);
        $list = PermissionPathsList::fromArray([
            'directories' => ['/workspace', '/tmp'],
            'primary' => '/workspace',
        ]);
        $updatePrimary = PermissionPathsUpdatePrimaryParams::fromArray(['path' => '/tmp']);
        $workspaceParams = PermissionPathsWorkspaceCheckParams::fromArray(['path' => '/workspace/file.txt']);
        $workspaceResult = PermissionPathsWorkspaceCheckResult::fromArray(['allowed' => false]);
        $urlMode = PermissionUrlsSetUnrestrictedModeParams::fromArray(['enabled' => true]);

        expect($add->toArray())->toBe(['path' => '/workspace'])
            ->and($allowedParams->toArray())->toBe(['path' => '/workspace/file.txt'])
            ->and($allowedResult->toArray())->toBe(['allowed' => true])
            ->and($list->toArray())->toBe([
                'directories' => ['/workspace', '/tmp'],
                'primary' => '/workspace',
            ])
            ->and($updatePrimary->toArray())->toBe(['path' => '/tmp'])
            ->and($workspaceParams->toArray())->toBe(['path' => '/workspace/file.txt'])
            ->and($workspaceResult->toArray())->toBe(['allowed' => false])
            ->and($urlMode->toArray())->toBe(['enabled' => true]);
    });
});
