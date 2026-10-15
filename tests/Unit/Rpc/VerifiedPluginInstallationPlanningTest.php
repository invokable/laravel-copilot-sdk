<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogReferenceInstallationOutcomeKind;
use Revolution\Copilot\Enums\CatalogReferenceInstallationResultKind;
use Revolution\Copilot\Enums\PluginInstallationManagementOutcomeKind;
use Revolution\Copilot\Enums\PluginInstallationOutcomeKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCatalog;
use Revolution\Copilot\Rpc\PendingServerPlugins;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogMaterializationReference;
use Revolution\Copilot\Types\Rpc\CatalogPlanInstallFromReferenceRequest;
use Revolution\Copilot\Types\Rpc\CatalogReferenceInstallationResult;
use Revolution\Copilot\Types\Rpc\PluginApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginInstallationManagementResult;
use Revolution\Copilot\Types\Rpc\PluginInstallationResult;
use Revolution\Copilot\Types\Rpc\PluginInstallPlan;
use Revolution\Copilot\Types\Rpc\PluginPlanInstallRequest;

function verifiedPluginInstallationPlanData(): array
{
    return [
        'expiresAt' => '2026-10-15T10:00:00Z',
        'operationId' => 'operation-1',
        'planHandle' => 'plan-1',
        'review' => [
            'action' => 'install',
            'catalogue' => ['displayName' => 'Example Plugin', 'source' => 'Agent Finder'],
            'files' => [],
            'installsDisabled' => true,
            'manifestContent' => '{}',
            'manifestPath' => 'plugin.json',
            'name' => 'example-plugin',
            'source' => [
                'bundleDigest' => 'sha256:bundle',
                'catalogRevisionId' => 'revision-1',
                'descriptorDigest' => 'sha256:descriptor',
                'repository' => 'owner/repo',
                'repositoryId' => '12345',
                'resourceId' => 'resource-1',
                'revision' => 'abcdef',
                'root' => '',
            ],
            'target' => [
                'displayLabel' => 'installed-plugins/example-plugin',
                'relativePath' => 'installed-plugins/example-plugin',
            ],
            'totalBytes' => 2,
        ],
    ];
}

function verifiedPluginInstallationSummaryData(): array
{
    return [
        'catalogue' => ['displayName' => 'Example Plugin', 'source' => 'Agent Finder'],
        'enabled' => false,
        'installedAt' => '2026-10-15T09:00:00Z',
        'name' => 'example-plugin',
        'operationId' => 'operation-1',
        'source' => verifiedPluginInstallationPlanData()['review']['source'],
        'target' => verifiedPluginInstallationPlanData()['review']['target'],
    ];
}

describe('verified Agent Plugin installation planning RPCs', function () {
    it('plans a selected plugin candidate and decodes the reviewable plan', function () {
        $contract = new CatalogClientContract(3, ['agent-plugin-confirmed-installation']);
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('plugins.planInstall', [
                'candidateHandle' => 'candidate-1',
                'contract' => $contract->toArray(),
                'policySessionId' => 'session-1',
            ])
            ->andReturn($response = [
                'kind' => 'outcome',
                'negotiated' => [
                    'runtimeProtocolVersion' => 3,
                    'grantedCapabilities' => ['agent-plugin-confirmed-installation'],
                ],
                'outcome' => [
                    'kind' => 'install-planned',
                    'plan' => verifiedPluginInstallationPlanData(),
                ],
            ], $response);

        $plugins = new PendingServerPlugins($client);
        $typedResult = $plugins->planInstall(new PluginPlanInstallRequest('candidate-1', $contract, 'session-1'));
        $arrayResult = $plugins->planInstall([
            'candidateHandle' => 'candidate-1',
            'contract' => $contract->toArray(),
            'policySessionId' => 'session-1',
        ]);

        expect($typedResult)->toBeInstanceOf(PluginInstallationManagementResult::class)
            ->and($typedResult->kind)->toBe(CatalogReferenceInstallationResultKind::Outcome)
            ->and($typedResult->outcome->kind)->toBe(PluginInstallationManagementOutcomeKind::InstallPlanned)
            ->and($typedResult->outcome->plan)->toBeInstanceOf(PluginInstallPlan::class)
            ->and($typedResult->outcome->plan->planHandle)->toBe('plan-1')
            ->and($typedResult->outcome->plan->review->name)->toBe('example-plugin')
            ->and($arrayResult->outcome->plan->planHandle)->toBe('plan-1');
    });

    it('applies a plugin plan and decodes the terminal installation outcome', function () {
        $contract = new CatalogClientContract(3, ['agent-plugin-confirmed-installation']);
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('plugins.applyInstall', [
                'contract' => $contract->toArray(),
                'planHandle' => 'plan-1',
                'policySessionId' => 'session-1',
            ])
            ->andReturn($response = [
                'kind' => 'outcome',
                'negotiated' => [
                    'runtimeProtocolVersion' => 3,
                    'grantedCapabilities' => ['agent-plugin-confirmed-installation'],
                ],
                'outcome' => [
                    'kind' => 'installed',
                    'installation' => verifiedPluginInstallationSummaryData(),
                    'operationId' => 'operation-1',
                ],
            ], $response);

        $plugins = new PendingServerPlugins($client);
        $typedResult = $plugins->applyInstall(new PluginApplyInstallRequest($contract, 'plan-1', 'session-1'));
        $arrayResult = $plugins->applyInstall([
            'contract' => $contract->toArray(),
            'planHandle' => 'plan-1',
            'policySessionId' => 'session-1',
        ]);

        expect($typedResult)->toBeInstanceOf(PluginInstallationResult::class)
            ->and($typedResult->outcome->kind)->toBe(PluginInstallationOutcomeKind::Installed)
            ->and($typedResult->outcome->installation->name)->toBe('example-plugin')
            ->and($typedResult->outcome->installation->enabled)->toBeFalse()
            ->and($typedResult->outcome->operationId)->toBe('operation-1')
            ->and($arrayResult->outcome->installation->name)->toBe('example-plugin');
    });

    it('plans a materialisation reference and types plugin but preserves skill plans', function () {
        $contract = new CatalogClientContract(3, [
            'catalog-reference-installation',
            'agent-plugin-confirmed-installation',
        ]);
        $reference = new CatalogMaterializationReference(
            resourceId: 'resource-1',
            revisionId: 'revision-1',
            schemaVersion: '1.0.0',
            descriptorDigest: 'sha256:descriptor',
            bundleDigest: 'sha256:bundle',
        );
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with(
                'catalog.planInstallFromReference',
                [
                    'contract' => $contract->toArray(),
                    'policySessionId' => 'session-1',
                    'reference' => $reference->toArray(),
                ],
            )
            ->andReturn(
                [
                    'kind' => 'outcome',
                    'negotiated' => [
                        'runtimeProtocolVersion' => 3,
                        'grantedCapabilities' => [
                            'catalog-reference-installation',
                            'agent-plugin-confirmed-installation',
                        ],
                    ],
                    'outcome' => [
                        'kind' => 'plugin-install-planned',
                        'plan' => verifiedPluginInstallationPlanData(),
                    ],
                ],
                [
                    'kind' => 'outcome',
                    'outcome' => [
                        'kind' => 'skill-install-planned',
                        'plan' => ['planHandle' => 'skill-plan-1'],
                    ],
                ],
            );
        $catalog = new PendingCatalog($client);

        $pluginResult = $catalog->planInstallFromReference(
            new CatalogPlanInstallFromReferenceRequest($contract, 'session-1', $reference),
        );
        $skillResult = $catalog->planInstallFromReference([
            'contract' => $contract->toArray(),
            'policySessionId' => 'session-1',
            'reference' => $reference->toArray(),
        ]);

        expect($pluginResult)->toBeInstanceOf(CatalogReferenceInstallationResult::class)
            ->and($pluginResult->outcome->kind)->toBe(CatalogReferenceInstallationOutcomeKind::PluginInstallPlanned)
            ->and($pluginResult->outcome->plan)->toBeInstanceOf(PluginInstallPlan::class)
            ->and($skillResult->outcome->kind)->toBe(CatalogReferenceInstallationOutcomeKind::SkillInstallPlanned)
            ->and($skillResult->outcome->plan)->toBe(['planHandle' => 'skill-plan-1']);
    });
});
