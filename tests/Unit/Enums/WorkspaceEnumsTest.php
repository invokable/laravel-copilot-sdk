<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\HostType;
use Revolution\Copilot\Enums\SessionSyncLevel;

describe('HostType', function () {
    it('has github case', function () {
        expect(HostType::GITHUB->value)->toBe('github');
    });

    it('has ado case', function () {
        expect(HostType::ADO->value)->toBe('ado');
    });

    it('can be created from string', function () {
        expect(HostType::from('github'))->toBe(HostType::GITHUB)
            ->and(HostType::from('ado'))->toBe(HostType::ADO);
    });

    it('returns null for invalid value', function () {
        expect(HostType::tryFrom('invalid'))->toBeNull();
    });
});

describe('SessionSyncLevel', function () {
    it('has local case', function () {
        expect(SessionSyncLevel::LOCAL->value)->toBe('local');
    });

    it('has user case', function () {
        expect(SessionSyncLevel::USER->value)->toBe('user');
    });

    it('has repo_and_user case', function () {
        expect(SessionSyncLevel::REPO_AND_USER->value)->toBe('repo_and_user');
    });

    it('can be created from string', function () {
        expect(SessionSyncLevel::from('local'))->toBe(SessionSyncLevel::LOCAL)
            ->and(SessionSyncLevel::from('user'))->toBe(SessionSyncLevel::USER)
            ->and(SessionSyncLevel::from('repo_and_user'))->toBe(SessionSyncLevel::REPO_AND_USER);
    });

    it('returns null for invalid value', function () {
        expect(SessionSyncLevel::tryFrom('invalid'))->toBeNull();
    });
});
