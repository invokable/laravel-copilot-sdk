<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SkillInstallationFailureReason;
use Revolution\Copilot\Enums\SkillInstallationManagementOutcomeKind;
use Revolution\Copilot\Enums\SkillInstallationManagementResultKind;
use Revolution\Copilot\Types\Rpc\SkillInstallationManagementOutcome;
use Revolution\Copilot\Types\Rpc\SkillInstallationManagementResult;

it('decodes skill refusal reasons and the GitHub retry interval', function () {
    $result = SkillInstallationManagementResult::fromArray([
        'kind' => 'outcome',
        'outcome' => [
            'kind' => 'refused',
            'reason' => 'source-rate-limited',
            'retryAfterSeconds' => 60,
        ],
    ]);

    expect($result->kind)->toBe(SkillInstallationManagementResultKind::Outcome)
        ->and($result->outcome)->toBeInstanceOf(SkillInstallationManagementOutcome::class)
        ->and($result->outcome->kind)->toBe(SkillInstallationManagementOutcomeKind::Refused)
        ->and($result->outcome->reason)->toBe(SkillInstallationFailureReason::SourceRateLimited)
        ->and($result->outcome->retryAfterSeconds)->toBe(60)
        ->and($result->toArray())->toBe([
            'kind' => 'outcome',
            'outcome' => [
                'kind' => 'refused',
                'reason' => 'source-rate-limited',
                'retryAfterSeconds' => 60,
            ],
        ]);
});

it('decodes credential refusal reasons without a retry interval', function (string $reason, SkillInstallationFailureReason $expectedReason) {
    $outcome = SkillInstallationManagementOutcome::fromArray([
        'kind' => 'refused',
        'reason' => $reason,
    ]);

    expect($outcome->reason)->toBe($expectedReason)
        ->and($outcome->retryAfterSeconds)->toBeNull()
        ->and($outcome->toArray())->toBe([
            'kind' => 'refused',
            'reason' => $reason,
        ]);
})->with([
    'no credential' => ['no-credential', SkillInstallationFailureReason::NoCredential],
    'credential rejected' => ['credential-rejected', SkillInstallationFailureReason::CredentialRejected],
]);

it('preserves variant-specific skill installation payloads', function () {
    $data = [
        'kind' => 'install-planned',
        'plan' => ['planHandle' => 'plan-1'],
        'review' => ['name' => 'example-skill'],
    ];
    $outcome = SkillInstallationManagementOutcome::fromArray($data);

    expect($outcome->plan)->toBe(['planHandle' => 'plan-1'])
        ->and($outcome->data)->toBe(['review' => ['name' => 'example-skill']])
        ->and($outcome->toArray())->toBe([
            'kind' => 'install-planned',
            'plan' => ['planHandle' => 'plan-1'],
            'review' => ['name' => 'example-skill'],
        ]);
});
