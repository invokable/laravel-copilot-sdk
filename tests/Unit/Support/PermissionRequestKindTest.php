<?php

declare(strict_types=1);

use Revolution\Copilot\Support\PermissionRequestKind;

describe('PermissionRequestKind', function () {
    it('approved', function () {
        expect(PermissionRequestKind::approved())->toContain('approved');
    });

    it('deniedByRules', function () {
        expect(PermissionRequestKind::deniedByRules())->toContain('denied-by-rules');
    });

    it('deniedNoApprovalRuleAndCouldNotRequestFromUser', function () {
        expect(PermissionRequestKind::deniedNoApprovalRuleAndCouldNotRequestFromUser()['kind'])->toBe('denied-no-approval-rule-and-could-not-request-from-user');
    });

    it('deniedInteractivelyByUser', function () {
        expect(PermissionRequestKind::deniedInteractivelyByUser()['kind'])->toBe('denied-interactively-by-user');
    });

    it('deniedInteractivelyByUser with feedback', function () {
        $result = PermissionRequestKind::deniedInteractivelyByUser('Too risky');
        expect($result['kind'])->toBe('denied-interactively-by-user')
            ->and($result['feedback'])->toBe('Too risky');
    });

    it('deniedByContentExclusionPolicy', function () {
        $result = PermissionRequestKind::deniedByContentExclusionPolicy('/secret/file.txt', 'Excluded by policy');
        expect($result['kind'])->toBe('denied-by-content-exclusion-policy')
            ->and($result['path'])->toBe('/secret/file.txt')
            ->and($result['message'])->toBe('Excluded by policy');
    });

    it('select includes content exclusion policy', function () {
        expect(PermissionRequestKind::select())->toHaveKey('denied-by-content-exclusion-policy');
    });

    it('select', function () {
        expect(PermissionRequestKind::select())->toHaveKeys(['approved', 'denied-interactively-by-user']);
    });
});
