<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\TaskExecutionMode;
use Revolution\Copilot\Enums\TaskKind;
use Revolution\Copilot\Enums\TaskShellAttachmentMode;
use Revolution\Copilot\Enums\TaskStatus;

describe('TaskKind', function () {
    it('has correct values', function () {
        expect(TaskKind::AGENT->value)->toBe('agent')
            ->and(TaskKind::CLIENT->value)->toBe('client')
            ->and(TaskKind::SHELL->value)->toBe('shell');
    });
});

describe('TaskExecutionMode', function () {
    it('has sync case', function () {
        expect(TaskExecutionMode::Sync->value)->toBe('sync');
    });

    it('has background case', function () {
        expect(TaskExecutionMode::Background->value)->toBe('background');
    });

    it('can create from string', function () {
        expect(TaskExecutionMode::from('sync'))->toBe(TaskExecutionMode::Sync)
            ->and(TaskExecutionMode::from('background'))->toBe(TaskExecutionMode::Background);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(TaskExecutionMode::tryFrom('unknown'))->toBeNull();
    });
});

describe('TaskShellAttachmentMode', function () {
    it('has attached case', function () {
        expect(TaskShellAttachmentMode::Attached->value)->toBe('attached');
    });

    it('has detached case', function () {
        expect(TaskShellAttachmentMode::Detached->value)->toBe('detached');
    });

    it('can create from string', function () {
        expect(TaskShellAttachmentMode::from('attached'))->toBe(TaskShellAttachmentMode::Attached)
            ->and(TaskShellAttachmentMode::from('detached'))->toBe(TaskShellAttachmentMode::Detached);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(TaskShellAttachmentMode::tryFrom('unknown'))->toBeNull();
    });
});

describe('TaskStatus', function () {
    it('has running case', function () {
        expect(TaskStatus::Running->value)->toBe('running');
    });

    it('has idle case', function () {
        expect(TaskStatus::Idle->value)->toBe('idle');
    });

    it('has completed case', function () {
        expect(TaskStatus::Completed->value)->toBe('completed');
    });

    it('has failed case', function () {
        expect(TaskStatus::Failed->value)->toBe('failed');
    });

    it('has cancelled case', function () {
        expect(TaskStatus::Cancelled->value)->toBe('cancelled');
    });

    it('can create all cases from string', function () {
        expect(TaskStatus::from('running'))->toBe(TaskStatus::Running)
            ->and(TaskStatus::from('idle'))->toBe(TaskStatus::Idle)
            ->and(TaskStatus::from('completed'))->toBe(TaskStatus::Completed)
            ->and(TaskStatus::from('failed'))->toBe(TaskStatus::Failed)
            ->and(TaskStatus::from('cancelled'))->toBe(TaskStatus::Cancelled);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(TaskStatus::tryFrom('unknown'))->toBeNull();
    });
});
