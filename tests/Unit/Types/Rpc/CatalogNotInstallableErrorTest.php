<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogNotInstallableReason;
use Revolution\Copilot\Types\Rpc\CatalogNotInstallableError;

describe('CatalogNotInstallableError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogNotInstallableError(
            reason: CatalogNotInstallableReason::PolicyForbids,
            message: 'Policy forbids installing this candidate.',
        );

        expect($error->kind)->toBe('not-installable')
            ->and($error->reason)->toBe(CatalogNotInstallableReason::PolicyForbids)
            ->and($error->message)->toBe('Policy forbids installing this candidate.');
    });

    it('can be created from array', function () {
        $error = CatalogNotInstallableError::fromArray([
            'reason' => 'ai-skill-not-installable',
            'message' => 'AI skills cannot be installed through this surface.',
        ]);

        expect($error->kind)->toBe('not-installable')
            ->and($error->reason)->toBe(CatalogNotInstallableReason::AiSkillNotInstallable)
            ->and($error->message)->toBe('AI skills cannot be installed through this surface.');
    });

    it('converts to array using the reason wire value', function () {
        $error = new CatalogNotInstallableError(
            reason: CatalogNotInstallableReason::KindNotInstallable,
            message: 'This kind is not installable.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'not-installable',
            'reason' => 'kind-not-installable',
            'message' => 'This kind is not installable.',
        ]);
    });

    it('rejects an unknown reason when created from array', function () {
        expect(fn () => CatalogNotInstallableError::fromArray([
            'reason' => 'unknown',
            'message' => 'Not installable.',
        ]))->toThrow(ValueError::class);
    });
});
