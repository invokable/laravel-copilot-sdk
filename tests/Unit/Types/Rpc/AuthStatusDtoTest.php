<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AuthStatusDto;

describe('AuthStatusDto', function () {
    it('creates from the wire payload with all fields', function () {
        $status = AuthStatusDto::fromArray([
            'isAuthenticated' => true,
            'accountCount' => 2,
            'activeHost' => 'github.com',
            'activeLogin' => 'octocat',
            'copilotPlan' => 'individual',
        ]);

        expect($status->isAuthenticated)->toBeTrue()
            ->and($status->accountCount)->toBe(2)
            ->and($status->activeHost)->toBe('github.com')
            ->and($status->activeLogin)->toBe('octocat')
            ->and($status->copilotPlan)->toBe('individual');
    });

    it('uses defaults when the wire payload omits fields', function () {
        $status = AuthStatusDto::fromArray([]);

        expect($status->isAuthenticated)->toBeFalse()
            ->and($status->accountCount)->toBe(0)
            ->and($status->activeHost)->toBeNull()
            ->and($status->activeLogin)->toBeNull()
            ->and($status->copilotPlan)->toBeNull();
    });

    it('casts scalar values to the declared types', function () {
        $status = AuthStatusDto::fromArray([
            'isAuthenticated' => 1,
            'accountCount' => '3',
        ]);

        expect($status->isAuthenticated)->toBeTrue()
            ->and($status->accountCount)->toBe(3);
    });

    it('omits null optional fields when converted to array', function () {
        $status = new AuthStatusDto(isAuthenticated: false, accountCount: 0);

        expect($status->toArray())->toBe([
            'isAuthenticated' => false,
            'accountCount' => 0,
        ]);
    });

    it('converts all fields to array', function () {
        $status = AuthStatusDto::fromArray([
            'isAuthenticated' => true,
            'accountCount' => 1,
            'activeHost' => 'github.com',
            'activeLogin' => 'octocat',
            'copilotPlan' => 'business',
        ]);

        expect($status->toArray())->toBe([
            'isAuthenticated' => true,
            'accountCount' => 1,
            'activeHost' => 'github.com',
            'activeLogin' => 'octocat',
            'copilotPlan' => 'business',
        ]);
    });

    it('round-trips through toArray and fromArray', function () {
        $original = AuthStatusDto::fromArray([
            'isAuthenticated' => true,
            'accountCount' => 1,
            'activeLogin' => 'octocat',
        ]);

        $restored = AuthStatusDto::fromArray($original->toArray());

        expect($restored)->toEqual($original);
    });
});
