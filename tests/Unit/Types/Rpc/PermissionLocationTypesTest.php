<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionLocationType;
use Revolution\Copilot\Types\Rpc\FolderTrustAddParams;
use Revolution\Copilot\Types\Rpc\FolderTrustAddResult;
use Revolution\Copilot\Types\Rpc\FolderTrustCheckParams;
use Revolution\Copilot\Types\Rpc\FolderTrustCheckResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationAddToolApprovalParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationAddToolApprovalResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationApplyParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationApplyResult;
use Revolution\Copilot\Types\Rpc\PermissionLocationResolveParams;
use Revolution\Copilot\Types\Rpc\PermissionLocationResolveResult;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsCommands;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsCustomTool;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsExtensionEnvAccess;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsExtensionManagement;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsExtensionPermissionAccess;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsMcp;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsMcpSampling;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsMemory;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsRead;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsWorkflow;
use Revolution\Copilot\Types\Rpc\PermissionsLocationsAddToolApprovalDetailsWrite;

describe('permission location and folder trust types', function () {
    it('maps location and folder trust request and result types', function () {
        $resolveParams = PermissionLocationResolveParams::fromArray(['workingDirectory' => '/workspace/repo']);
        $resolveResult = PermissionLocationResolveResult::fromArray([
            'locationKey' => 'repo:/workspace/repo',
            'locationType' => 'repo',
        ]);
        $applyParams = PermissionLocationApplyParams::fromArray(['workingDirectory' => '/workspace/repo']);
        $applyResult = PermissionLocationApplyResult::fromArray([
            'appliedDirectoryCount' => 2,
            'appliedRuleCount' => 1,
            'appliedRules' => [['tool' => 'bash', 'decision' => 'allow']],
            'changed' => true,
            'locationKey' => 'repo:/workspace/repo',
            'locationType' => 'dir',
        ]);
        $approvalParams = PermissionLocationAddToolApprovalParams::fromArray([
            'approval' => ['kind' => 'read'],
            'locationKey' => 'repo:/workspace/repo',
        ]);
        $approvalResult = PermissionLocationAddToolApprovalResult::fromArray(['success' => true]);
        $checkParams = FolderTrustCheckParams::fromArray(['path' => '/workspace/repo']);
        $checkResult = FolderTrustCheckResult::fromArray(['trusted' => true]);
        $addParams = FolderTrustAddParams::fromArray(['path' => '/workspace/repo']);
        $addResult = FolderTrustAddResult::fromArray(['success' => true]);

        expect(PermissionLocationType::Repo->value)->toBe('repo')
            ->and(PermissionLocationType::Dir->value)->toBe('dir')
            ->and($resolveParams->toArray())->toBe(['workingDirectory' => '/workspace/repo'])
            ->and($resolveResult->locationType)->toBe(PermissionLocationType::Repo)
            ->and($resolveResult->toArray())->toBe([
                'locationKey' => 'repo:/workspace/repo',
                'locationType' => 'repo',
            ])
            ->and($applyParams->toArray())->toBe(['workingDirectory' => '/workspace/repo'])
            ->and($applyResult->locationType)->toBe(PermissionLocationType::Dir)
            ->and($applyResult->toArray())->toBe([
                'appliedDirectoryCount' => 2,
                'appliedRuleCount' => 1,
                'appliedRules' => [['tool' => 'bash', 'decision' => 'allow']],
                'changed' => true,
                'locationKey' => 'repo:/workspace/repo',
                'locationType' => 'dir',
            ])
            ->and($approvalParams->toArray())->toBe([
                'approval' => ['kind' => 'read'],
                'locationKey' => 'repo:/workspace/repo',
            ])
            ->and($approvalResult->toArray())->toBe(['success' => true])
            ->and($checkParams->toArray())->toBe(['path' => '/workspace/repo'])
            ->and($checkResult->toArray())->toBe(['trusted' => true])
            ->and($addParams->toArray())->toBe(['path' => '/workspace/repo'])
            ->and($addResult->toArray())->toBe(['success' => true]);
    });

    it('maps each tagged location tool approval variant', function () {
        $cases = [
            [
                ['kind' => 'commands', 'commandIdentifiers' => ['bash']],
                PermissionsLocationsAddToolApprovalDetailsCommands::class,
            ],
            [
                ['kind' => 'read'],
                PermissionsLocationsAddToolApprovalDetailsRead::class,
            ],
            [
                ['kind' => 'write'],
                PermissionsLocationsAddToolApprovalDetailsWrite::class,
            ],
            [
                ['kind' => 'mcp', 'serverName' => 'server', 'toolName' => null],
                PermissionsLocationsAddToolApprovalDetailsMcp::class,
            ],
            [
                ['kind' => 'mcp-sampling', 'serverName' => 'server'],
                PermissionsLocationsAddToolApprovalDetailsMcpSampling::class,
            ],
            [
                ['kind' => 'memory'],
                PermissionsLocationsAddToolApprovalDetailsMemory::class,
            ],
            [
                ['kind' => 'custom-tool', 'toolName' => 'search'],
                PermissionsLocationsAddToolApprovalDetailsCustomTool::class,
            ],
            [
                ['kind' => 'extension-management'],
                PermissionsLocationsAddToolApprovalDetailsExtensionManagement::class,
            ],
            [
                ['kind' => 'workflow'],
                PermissionsLocationsAddToolApprovalDetailsWorkflow::class,
            ],
            [
                ['kind' => 'extension-permission-access', 'extensionName' => 'extension'],
                PermissionsLocationsAddToolApprovalDetailsExtensionPermissionAccess::class,
            ],
            [
                ['kind' => 'extension-env-access', 'extensionName' => 'extension', 'environmentVariables' => ['TOKEN']],
                PermissionsLocationsAddToolApprovalDetailsExtensionEnvAccess::class,
            ],
        ];

        foreach ($cases as [$approval, $expectedClass]) {
            $params = PermissionLocationAddToolApprovalParams::fromArray([
                'approval' => $approval,
                'locationKey' => 'repo:/workspace/repo',
            ]);

            expect($params->approval)->toBeInstanceOf($expectedClass)
                ->and($params->toArray()['approval'])->toBe($approval);
        }
    });

    it('preserves populated optional approval details and unknown location types', function () {
        $extensionManagement = PermissionLocationAddToolApprovalParams::fromArray([
            'approval' => ['kind' => 'extension-management', 'operation' => 'install'],
            'locationKey' => 'repo:/workspace/repo',
        ]);
        $workflow = PermissionLocationAddToolApprovalParams::fromArray([
            'approval' => ['kind' => 'workflow', 'approvalKey' => 'workflow-key'],
            'locationKey' => 'repo:/workspace/repo',
        ]);
        $unknownType = PermissionLocationResolveResult::fromArray([
            'locationKey' => 'future:/workspace/repo',
            'locationType' => 'future',
        ]);

        expect($extensionManagement->toArray()['approval'])->toBe([
            'kind' => 'extension-management',
            'operation' => 'install',
        ])
            ->and($workflow->toArray()['approval'])->toBe([
                'kind' => 'workflow',
                'approvalKey' => 'workflow-key',
            ])
            ->and($unknownType->locationType)->toBe('future')
            ->and($unknownType->toArray()['locationType'])->toBe('future');
    });
});
