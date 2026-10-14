<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\SkillInstallationFailureReason;
use Revolution\Copilot\Types\Rpc\CatalogClientContract;
use Revolution\Copilot\Types\Rpc\CatalogMaterializationReference;
use Revolution\Copilot\Types\Rpc\CatalogNegotiatedContract;
use Revolution\Copilot\Types\Rpc\SkillPlanInstallFromReferenceRequest;

it('round-trips a materialisation reference', function () {
    $data = [
        'resourceId' => '12345',
        'revisionId' => '67890',
        'schemaVersion' => '1.0.0',
        'descriptorDigest' => 'sha256:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        'bundleDigest' => 'sha256:bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
    ];

    $reference = CatalogMaterializationReference::fromArray($data);

    expect($reference->toArray())->toBe($data);
});

it('round-trips a request with its nested contract and reference', function () {
    $data = [
        'contract' => [
            'protocolVersion' => 3,
            'requiredCapabilities' => ['skill-confirmed-installation', 'skill-reference-installation'],
        ],
        'reference' => [
            'resourceId' => '12345',
            'revisionId' => '67890',
            'schemaVersion' => '1.0.0',
            'descriptorDigest' => 'sha256:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'bundleDigest' => 'sha256:bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
        ],
        'policySessionId' => 'session-1',
    ];

    $request = SkillPlanInstallFromReferenceRequest::fromArray($data);

    expect($request->contract)->toBeInstanceOf(CatalogClientContract::class)
        ->and($request->reference)->toBeInstanceOf(CatalogMaterializationReference::class)
        ->and($request->toArray())->toBe($data);
});

it('exposes reference-installation capabilities and refusal reasons', function () {
    $negotiated = CatalogNegotiatedContract::fromArray([
        'runtimeProtocolVersion' => 3,
        'grantedCapabilities' => ['skill-confirmed-installation', 'skill-reference-installation'],
    ]);

    expect(CatalogCapability::SkillConfirmedInstallation->value)->toBe('skill-confirmed-installation')
        ->and(CatalogCapability::SkillReferenceInstallation->value)->toBe('skill-reference-installation')
        ->and($negotiated->grantedCapabilities)->toBe([
            CatalogCapability::SkillConfirmedInstallation,
            CatalogCapability::SkillReferenceInstallation,
        ])
        ->and(SkillInstallationFailureReason::InvalidReference->value)->toBe('invalid-reference')
        ->and(SkillInstallationFailureReason::DescriptorNotFound->value)->toBe('descriptor-not-found')
        ->and(SkillInstallationFailureReason::DescriptorWithdrawn->value)->toBe('descriptor-withdrawn')
        ->and(SkillInstallationFailureReason::DigestMismatch->value)->toBe('digest-mismatch')
        ->and(SkillInstallationFailureReason::UnsupportedResource->value)->toBe('unsupported-resource');
});
