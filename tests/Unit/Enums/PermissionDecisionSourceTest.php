<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionDecisionSource;

describe('PermissionDecisionSource', function () {
    it('has correct string values', function () {
        expect(PermissionDecisionSource::JUDGE_RECOMMENDATION->value)->toBe('judge_recommendation')
            ->and(PermissionDecisionSource::HUMAN_RESPONSE->value)->toBe('human_response')
            ->and(PermissionDecisionSource::HOST_POLICY->value)->toBe('host_policy')
            ->and(PermissionDecisionSource::UNATTENDED_FALLBACK->value)->toBe('unattended_fallback');
    });

    it('can be created from string', function () {
        expect(PermissionDecisionSource::from('judge_recommendation'))->toBe(PermissionDecisionSource::JUDGE_RECOMMENDATION)
            ->and(PermissionDecisionSource::from('human_response'))->toBe(PermissionDecisionSource::HUMAN_RESPONSE)
            ->and(PermissionDecisionSource::from('host_policy'))->toBe(PermissionDecisionSource::HOST_POLICY)
            ->and(PermissionDecisionSource::from('unattended_fallback'))->toBe(PermissionDecisionSource::UNATTENDED_FALLBACK);
    });
});
