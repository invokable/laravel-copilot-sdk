<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentMode;
use Revolution\Copilot\Enums\AttachmentType;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\Enums\ReferenceType;

describe('AgentMode', function () {
    it('has correct string values', function () {
        expect(AgentMode::AUTOPILOT->value)->toBe('autopilot')
            ->and(AgentMode::INTERACTIVE->value)->toBe('interactive')
            ->and(AgentMode::PLAN->value)->toBe('plan')
            ->and(AgentMode::SHELL->value)->toBe('shell');
    });

    it('can be created from string', function () {
        expect(AgentMode::from('autopilot'))->toBe(AgentMode::AUTOPILOT)
            ->and(AgentMode::from('interactive'))->toBe(AgentMode::INTERACTIVE)
            ->and(AgentMode::from('plan'))->toBe(AgentMode::PLAN)
            ->and(AgentMode::from('shell'))->toBe(AgentMode::SHELL);
    });

    it('has all expected cases', function () {
        expect(AgentMode::cases())->toHaveCount(4);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(AgentMode::tryFrom('invalid'))->toBeNull();
    });
});

describe('AttachmentType', function () {
    it('has correct string values', function () {
        expect(AttachmentType::DIRECTORY->value)->toBe('directory')
            ->and(AttachmentType::FILE->value)->toBe('file')
            ->and(AttachmentType::GITHUB_REFERENCE->value)->toBe('github_reference')
            ->and(AttachmentType::SELECTION->value)->toBe('selection')
            ->and(AttachmentType::BLOB->value)->toBe('blob');
    });

    it('can be created from string', function () {
        expect(AttachmentType::from('directory'))->toBe(AttachmentType::DIRECTORY)
            ->and(AttachmentType::from('file'))->toBe(AttachmentType::FILE)
            ->and(AttachmentType::from('github_reference'))->toBe(AttachmentType::GITHUB_REFERENCE)
            ->and(AttachmentType::from('selection'))->toBe(AttachmentType::SELECTION)
            ->and(AttachmentType::from('blob'))->toBe(AttachmentType::BLOB);
    });

    it('has all expected cases', function () {
        expect(AttachmentType::cases())->toHaveCount(5);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(AttachmentType::tryFrom('invalid'))->toBeNull();
    });
});

describe('ConnectionState', function () {
    it('has correct string values', function () {
        expect(ConnectionState::DISCONNECTED->value)->toBe('disconnected')
            ->and(ConnectionState::CONNECTING->value)->toBe('connecting')
            ->and(ConnectionState::CONNECTED->value)->toBe('connected')
            ->and(ConnectionState::ERROR->value)->toBe('error');
    });

    it('can be created from string', function () {
        expect(ConnectionState::from('disconnected'))->toBe(ConnectionState::DISCONNECTED)
            ->and(ConnectionState::from('connecting'))->toBe(ConnectionState::CONNECTING)
            ->and(ConnectionState::from('connected'))->toBe(ConnectionState::CONNECTED)
            ->and(ConnectionState::from('error'))->toBe(ConnectionState::ERROR);
    });

    it('has all expected cases', function () {
        expect(ConnectionState::cases())->toHaveCount(4);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(ConnectionState::tryFrom('invalid'))->toBeNull();
    });
});

describe('LogLevel', function () {
    it('has correct string values', function () {
        expect(LogLevel::INFO->value)->toBe('info')
            ->and(LogLevel::WARNING->value)->toBe('warning')
            ->and(LogLevel::ERROR->value)->toBe('error');
    });

    it('can be created from string', function () {
        expect(LogLevel::from('info'))->toBe(LogLevel::INFO)
            ->and(LogLevel::from('warning'))->toBe(LogLevel::WARNING)
            ->and(LogLevel::from('error'))->toBe(LogLevel::ERROR);
    });

    it('has all expected cases', function () {
        expect(LogLevel::cases())->toHaveCount(3);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(LogLevel::tryFrom('debug'))->toBeNull();
    });
});

describe('ReferenceType', function () {
    it('has correct string values', function () {
        expect(ReferenceType::DISCUSSION->value)->toBe('discussion')
            ->and(ReferenceType::ISSUE->value)->toBe('issue')
            ->and(ReferenceType::PR->value)->toBe('pr');
    });

    it('can be created from string', function () {
        expect(ReferenceType::from('discussion'))->toBe(ReferenceType::DISCUSSION)
            ->and(ReferenceType::from('issue'))->toBe(ReferenceType::ISSUE)
            ->and(ReferenceType::from('pr'))->toBe(ReferenceType::PR);
    });

    it('has all expected cases', function () {
        expect(ReferenceType::cases())->toHaveCount(3);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(ReferenceType::tryFrom('invalid'))->toBeNull();
    });
});
