<?php

declare(strict_types=1);

use Revolution\Copilot\Support\PermissionDecision;

describe('PermissionDecision', function () {
    it('builds approval decisions', function () {
        expect(PermissionDecision::approveOnce())->toBe(['kind' => 'approve-once'])
            ->and(PermissionDecision::approveForSession())->toBe(['kind' => 'approve-for-session'])
            ->and(PermissionDecision::approveForLocation())->toBe(['kind' => 'approve-for-location'])
            ->and(PermissionDecision::approvePermanently('example.com'))->toBe([
                'kind' => 'approve-permanently',
                'domain' => 'example.com',
            ]);
    });

    it('builds rejection decisions', function () {
        expect(PermissionDecision::reject())->toBe(['kind' => 'reject'])
            ->and(PermissionDecision::reject('not allowed'))->toBe([
                'kind' => 'reject',
                'feedback' => 'not allowed',
            ])
            ->and(PermissionDecision::userNotAvailable())->toBe(['kind' => 'user-not-available'])
            ->and(PermissionDecision::noResult())->toBe(['kind' => 'no-result']);
    });

    it('provides select choices', function () {
        expect(PermissionDecision::select())->toHaveKeys([
            'approve-once',
            'approve-for-session',
            'approve-for-location',
            'reject',
        ]);
    });
});
