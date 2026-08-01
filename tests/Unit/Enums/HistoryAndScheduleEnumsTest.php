<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryPhaseStatus;
use Revolution\Copilot\Enums\HistoryFileRestoreSkipReason;
use Revolution\Copilot\Enums\HistoryRewindChangeType;
use Revolution\Copilot\Enums\HistoryRewindMode;
use Revolution\Copilot\Enums\HistoryRewindOutcome;
use Revolution\Copilot\Enums\HistoryRewindUnavailableReason;
use Revolution\Copilot\Enums\ScheduleOrigin;
use Revolution\Copilot\Enums\SendAgentMode;

describe('FactoryPhaseStatus', function () {
    it('has expected cases', function () {
        expect(FactoryPhaseStatus::PENDING->value)->toBe('pending')
            ->and(FactoryPhaseStatus::ACTIVE->value)->toBe('active')
            ->and(FactoryPhaseStatus::COMPLETED->value)->toBe('completed')
            ->and(FactoryPhaseStatus::SKIPPED->value)->toBe('skipped');
    });
});

describe('HistoryFileRestoreSkipReason', function () {
    it('maps kebab-case values', function () {
        expect(HistoryFileRestoreSkipReason::USER_MODIFIED->value)->toBe('user-modified')
            ->and(HistoryFileRestoreSkipReason::SKIPPED_CAPTURE->value)->toBe('skipped-capture');
    });
});

describe('HistoryRewindChangeType', function () {
    it('has expected cases', function () {
        expect(HistoryRewindChangeType::CREATED->value)->toBe('created')
            ->and(HistoryRewindChangeType::DELETED->value)->toBe('deleted')
            ->and(HistoryRewindChangeType::MODIFIED->value)->toBe('modified');
    });
});

describe('HistoryRewindMode', function () {
    it('has expected cases', function () {
        expect(HistoryRewindMode::CONVERSATION->value)->toBe('conversation')
            ->and(HistoryRewindMode::CONVERSATION_AND_FILES->value)->toBe('conversation-and-files');
    });
});

describe('HistoryRewindOutcome', function () {
    it('maps every outcome value', function () {
        expect(HistoryRewindOutcome::SUCCESS->value)->toBe('success')
            ->and(HistoryRewindOutcome::SESSION_BUSY->value)->toBe('session-busy')
            ->and(HistoryRewindOutcome::FILE_CHANGE_TRACKING_DISABLED->value)->toBe('file-change-tracking-disabled')
            ->and(HistoryRewindOutcome::UNSUPPORTED_REMOTE_SESSION->value)->toBe('unsupported-remote-session')
            ->and(HistoryRewindOutcome::FILES_ROLLED_BACK->value)->toBe('files-rolled-back')
            ->and(HistoryRewindOutcome::ROLLBACK_INCOMPLETE->value)->toBe('rollback-incomplete')
            ->and(HistoryRewindOutcome::TRUNCATION_FAILED->value)->toBe('truncation-failed')
            ->and(HistoryRewindOutcome::CHECKPOINT_CLEANUP_FAILED->value)->toBe('checkpoint-cleanup-failed')
            ->and(HistoryRewindOutcome::SNAPSHOT_PRUNE_FAILED->value)->toBe('snapshot-prune-failed');
    });
});

describe('HistoryRewindUnavailableReason', function () {
    it('has expected cases', function () {
        expect(HistoryRewindUnavailableReason::FILE_CHANGE_TRACKING_DISABLED->value)->toBe('file-change-tracking-disabled')
            ->and(HistoryRewindUnavailableReason::SESSION_BUSY->value)->toBe('session-busy')
            ->and(HistoryRewindUnavailableReason::UNSUPPORTED_REMOTE_SESSION->value)->toBe('unsupported-remote-session');
    });
});

describe('ScheduleOrigin', function () {
    it('has expected cases', function () {
        expect(ScheduleOrigin::USER->value)->toBe('user')
            ->and(ScheduleOrigin::MODEL->value)->toBe('model');
    });
});

describe('SendAgentMode', function () {
    it('has expected cases', function () {
        expect(SendAgentMode::INTERACTIVE->value)->toBe('interactive')
            ->and(SendAgentMode::PLAN->value)->toBe('plan')
            ->and(SendAgentMode::AUTOPILOT->value)->toBe('autopilot')
            ->and(SendAgentMode::SHELL->value)->toBe('shell');
    });
});
