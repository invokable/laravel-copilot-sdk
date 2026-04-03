<?php

declare(strict_types=1);

use Revolution\Copilot\Support\PermissionRequestResultKind;

describe('PermissionRequestResultKind', function () {
    it('approved', function () {
        expect(PermissionRequestResultKind::approved())->toContain('approved');
    });

    it('deniedByRules', function () {
        expect(PermissionRequestResultKind::deniedByRules())->toContain('denied-by-rules');
    });

    it('deniedNoApprovalRuleAndCouldNotRequestFromUser', function () {
        expect(PermissionRequestResultKind::deniedNoApprovalRuleAndCouldNotRequestFromUser()['kind'])->toBe('denied-no-approval-rule-and-could-not-request-from-user');
    });

    it('deniedInteractivelyByUser', function () {
        expect(PermissionRequestResultKind::deniedInteractivelyByUser()['kind'])->toBe('denied-interactively-by-user');
    });

    it('deniedInteractivelyByUser with feedback', function () {
        $result = PermissionRequestResultKind::deniedInteractivelyByUser('Too risky');
        expect($result['kind'])->toBe('denied-interactively-by-user')
            ->and($result['feedback'])->toBe('Too risky');
    });

    it('deniedByContentExclusionPolicy', function () {
        $result = PermissionRequestResultKind::deniedByContentExclusionPolicy('/secret/file.txt', 'Excluded by policy');
        expect($result['kind'])->toBe('denied-by-content-exclusion-policy')
            ->and($result['path'])->toBe('/secret/file.txt')
            ->and($result['message'])->toBe('Excluded by policy');
    });

    it('deniedByPermissionRequestHook', function () {
        $result = PermissionRequestResultKind::deniedByPermissionRequestHook();
        expect($result['kind'])->toBe('denied-by-permission-request-hook')
            ->and($result)->not->toHaveKey('message')
            ->and($result)->not->toHaveKey('interrupt');
    });

    it('deniedByPermissionRequestHook with message', function () {
        $result = PermissionRequestResultKind::deniedByPermissionRequestHook('Blocked by hook');
        expect($result['kind'])->toBe('denied-by-permission-request-hook')
            ->and($result['message'])->toBe('Blocked by hook')
            ->and($result)->not->toHaveKey('interrupt');
    });

    it('deniedByPermissionRequestHook with message and interrupt', function () {
        $result = PermissionRequestResultKind::deniedByPermissionRequestHook('Blocked', true);
        expect($result['kind'])->toBe('denied-by-permission-request-hook')
            ->and($result['message'])->toBe('Blocked')
            ->and($result['interrupt'])->toBeTrue();
    });

    it('select includes content exclusion policy', function () {
        expect(PermissionRequestResultKind::select())->toHaveKey('denied-by-content-exclusion-policy');
    });

    it('select includes permission request hook', function () {
        expect(PermissionRequestResultKind::select())->toHaveKey('denied-by-permission-request-hook');
    });

    it('select', function () {
        expect(PermissionRequestResultKind::select())->toHaveKeys(['approved', 'denied-interactively-by-user']);
    });
});
