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

    it('select', function () {
        expect(PermissionRequestKind::select())->toHaveKeys(['approved', 'denied-interactively-by-user']);
    });
});
