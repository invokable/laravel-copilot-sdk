<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogInvalidRequestField;
use Revolution\Copilot\Enums\CatalogNegotiationRefusedReason;
use Revolution\Copilot\Enums\CatalogReferenceInstallationFailureReason;
use Revolution\Copilot\Enums\CatalogReferenceInstallationOutcomeKind;
use Revolution\Copilot\Enums\CatalogReferenceInstallationResultKind;
use Revolution\Copilot\Enums\PluginInstallationManagementOutcomeKind;
use Revolution\Copilot\Enums\PluginInstallationOutcomeKind;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogMaterializationReference;
use Revolution\Copilot\Types\Rpc\CatalogPlanInstallFromReferenceRequest;
use Revolution\Copilot\Types\Rpc\CatalogReferenceInstallationOutcome;
use Revolution\Copilot\Types\Rpc\CatalogReferenceInstallationResult;
use Revolution\Copilot\Types\Rpc\PluginApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginInstallationManagementOutcome;
use Revolution\Copilot\Types\Rpc\PluginInstallationManagementResult;
use Revolution\Copilot\Types\Rpc\PluginInstallationOutcome;
use Revolution\Copilot\Types\Rpc\PluginInstallationResult;
use Revolution\Copilot\Types\Rpc\PluginInstallPlan;
use Revolution\Copilot\Types\Rpc\PluginPlanInstallRequest;

function verifiedPluginTypesPlanData(): array
{
    return [
        'expiresAt' => '2026-10-15T10:00:00Z',
        'operationId' => 'operation-1',
        'planHandle' => 'plan-1',
        'review' => [
            'action' => 'install',
            'catalogue' => ['displayName' => 'Example Plugin', 'source' => 'Agent Finder'],
            'files' => [[
                'digest' => 'sha256:file',
                'executable' => false,
                'mediaType' => 'application/json',
                'path' => 'plugin.json',
                'sizeBytes' => 2,
            ]],
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
            'description' => 'Example',
            'version' => '1.0.0',
        ],
    ];
}

describe('verified Agent Plugin installation types', function () {
    it('round-trips all three install-planning requests', function () {
        $contract = new CatalogClientContract(3, ['agent-plugin-confirmed-installation']);
        $reference = new CatalogMaterializationReference('resource-1', 'revision-1', '1.0.0', 'sha256:descriptor', 'sha256:bundle');
        $requests = [
            [
                new PluginPlanInstallRequest('candidate-1', $contract, 'session-1'),
                [
                    'candidateHandle' => 'candidate-1',
                    'contract' => $contract->toArray(),
                    'policySessionId' => 'session-1',
                ],
                PluginPlanInstallRequest::class,
            ],
            [
                new PluginApplyInstallRequest($contract, 'plan-1', 'session-1'),
                [
                    'contract' => $contract->toArray(),
                    'planHandle' => 'plan-1',
                    'policySessionId' => 'session-1',
                ],
                PluginApplyInstallRequest::class,
            ],
            [
                new CatalogPlanInstallFromReferenceRequest($contract, 'session-1', $reference),
                [
                    'contract' => $contract->toArray(),
                    'policySessionId' => 'session-1',
                    'reference' => $reference->toArray(),
                ],
                CatalogPlanInstallFromReferenceRequest::class,
            ],
        ];

        foreach ($requests as [$request, $expected, $type]) {
            expect($request->toArray())->toBe($expected)
                ->and($type::fromArray($expected)->toArray())->toBe($expected);
        }

        expect(CatalogCapability::CatalogReferenceInstallation->value)
            ->toBe('catalog-reference-installation');
    });

    it('round-trips typed result and outcome discriminators with nested plugin plans', function () {
        $negotiated = [
            'runtimeProtocolVersion' => 3,
            'grantedCapabilities' => ['agent-plugin-confirmed-installation'],
        ];
        $plan = verifiedPluginTypesPlanData();
        $managementData = [
            'kind' => 'outcome',
            'negotiated' => $negotiated,
            'outcome' => [
                'kind' => 'install-planned',
                'plan' => $plan,
            ],
        ];
        $pluginResultData = [
            'kind' => 'outcome',
            'negotiated' => $negotiated,
            'outcome' => [
                'kind' => 'declined',
                'operationId' => 'operation-1',
                'reason' => 'confirmation-invalid',
            ],
        ];
        $referenceData = [
            'kind' => 'outcome',
            'outcome' => [
                'kind' => 'plugin-install-planned',
                'plan' => $plan,
            ],
        ];
        $management = PluginInstallationManagementResult::fromArray($managementData);
        $pluginResult = PluginInstallationResult::fromArray($pluginResultData);
        $referenceResult = CatalogReferenceInstallationResult::fromArray($referenceData);

        expect($management->kind)->toBe(CatalogReferenceInstallationResultKind::Outcome)
            ->and($management->outcome)->toBeInstanceOf(PluginInstallationManagementOutcome::class)
            ->and($management->outcome->kind)->toBe(PluginInstallationManagementOutcomeKind::InstallPlanned)
            ->and($management->outcome->plan)->toBeInstanceOf(PluginInstallPlan::class)
            ->and($management->outcome->plan->review->files[0]->sizeBytes)->toBe(2)
            ->and($management->toArray())->toBe($managementData)
            ->and($pluginResult->outcome)->toBeInstanceOf(PluginInstallationOutcome::class)
            ->and($pluginResult->outcome->kind)->toBe(PluginInstallationOutcomeKind::Declined)
            ->and($pluginResult->toArray())->toBe($pluginResultData)
            ->and($referenceResult->outcome)->toBeInstanceOf(CatalogReferenceInstallationOutcome::class)
            ->and($referenceResult->outcome->kind)->toBe(CatalogReferenceInstallationOutcomeKind::PluginInstallPlanned)
            ->and($referenceResult->outcome->plan)->toBeInstanceOf(PluginInstallPlan::class)
            ->and($referenceResult->toArray())->toBe($referenceData);
    });

    it('decodes negotiation and installation refusal variants', function () {
        $invalid = PluginInstallationManagementResult::fromArray([
            'kind' => 'invalid-request',
            'field' => 'policySessionId',
            'message' => 'The policy session is required.',
        ]);
        $negotiationRefused = PluginInstallationResult::fromArray([
            'kind' => 'negotiation-refused',
            'minimumSupportedProtocolVersion' => 3,
            'reason' => 'unsupported-capability',
            'runtimeProtocolVersion' => 3,
            'supportedCapabilities' => ['agent-plugin-confirmed-installation'],
            'unsupportedCapabilities' => ['future-capability'],
        ]);
        $referenceRefused = CatalogReferenceInstallationResult::fromArray([
            'kind' => 'outcome',
            'outcome' => [
                'kind' => 'refused',
                'reason' => 'invalid-reference',
            ],
        ]);

        expect($invalid->kind)->toBe(CatalogReferenceInstallationResultKind::InvalidRequest)
            ->and($invalid->field)->toBe(CatalogInvalidRequestField::PolicySessionId)
            ->and($negotiationRefused->kind)->toBe(CatalogReferenceInstallationResultKind::NegotiationRefused)
            ->and($negotiationRefused->reason)->toBe(CatalogNegotiationRefusedReason::UnsupportedCapability)
            ->and($referenceRefused->outcome->kind)->toBe(CatalogReferenceInstallationOutcomeKind::Refused)
            ->and($referenceRefused->outcome->reason)->toBe(CatalogReferenceInstallationFailureReason::InvalidReference);
    });
});
