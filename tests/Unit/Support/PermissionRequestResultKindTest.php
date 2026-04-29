<?php

declare(strict_types=1);

use Revolution\Copilot\Support\PermissionRequestResultKind;

describe('PermissionRequestResultKind', function () {
    it('approveOnce', function () {
        expect(PermissionRequestResultKind::approveOnce())->toBe(['kind' => 'approve-once']);
    });

    it('approveForSession', function () {
        expect(PermissionRequestResultKind::approveForSession())->toBe(['kind' => 'approve-for-session']);
    });

    it('approveForLocation', function () {
        expect(PermissionRequestResultKind::approveForLocation())->toBe(['kind' => 'approve-for-location']);
    });

    it('reject', function () {
        expect(PermissionRequestResultKind::reject())->toBe(['kind' => 'reject']);
    });

    it('userNotAvailable', function () {
        expect(PermissionRequestResultKind::userNotAvailable())->toBe(['kind' => 'user-not-available']);
    });

    it('noResult', function () {
        expect(PermissionRequestResultKind::noResult())->toBe(['kind' => 'no-result']);
    });

    it('select contains PermissionDecisionKind choices', function () {
        $select = PermissionRequestResultKind::select();

        expect($select)->toHaveKeys([
            'approve-once',
            'approve-for-session',
            'approve-for-location',
            'reject',
        ]);
    });

    it('select does not contain old deprecated values', function () {
        $select = PermissionRequestResultKind::select();

        expect($select)
            ->not->toHaveKey('approved')
            ->not->toHaveKey('denied-by-rules')
            ->not->toHaveKey('denied-interactively-by-user');
    });
});
