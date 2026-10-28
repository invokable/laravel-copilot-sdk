<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpPlanPolicySource;
use Revolution\Copilot\Types\Rpc\CatalogPolicyRejectedError;

describe('CatalogPolicyRejectedError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogPolicyRejectedError(
            source: McpPlanPolicySource::EnterpriseAllowlist,
            message: 'The server is not on the enterprise allowlist.',
        );

        expect($error->kind)->toBe('policy-rejected')
            ->and($error->source)->toBe(McpPlanPolicySource::EnterpriseAllowlist)
            ->and($error->message)->toBe('The server is not on the enterprise allowlist.');
    });

    it('can be created from array', function () {
        $error = CatalogPolicyRejectedError::fromArray([
            'source' => 'registry-policy',
            'message' => 'The registry policy rejected this server.',
        ]);

        expect($error->kind)->toBe('policy-rejected')
            ->and($error->source)->toBe(McpPlanPolicySource::RegistryPolicy)
            ->and($error->message)->toBe('The registry policy rejected this server.');
    });

    it('converts to array using the source wire value', function () {
        $error = new CatalogPolicyRejectedError(
            source: McpPlanPolicySource::LocalTrust,
            message: 'The server is not trusted locally.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'policy-rejected',
            'source' => 'local-trust',
            'message' => 'The server is not trusted locally.',
        ]);
    });

    it('rejects an unknown source when created from array', function () {
        expect(fn () => CatalogPolicyRejectedError::fromArray([
            'source' => 'unknown',
            'message' => 'The policy rejected this server.',
        ]))->toThrow(ValueError::class);
    });
});
