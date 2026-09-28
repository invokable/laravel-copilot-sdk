<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ConnectedRemoteSessionMetadataKind;
use Revolution\Copilot\Enums\HostType;
use Revolution\Copilot\Enums\ModelPickerCategory;
use Revolution\Copilot\Enums\ModelPickerPriceCategory;
use Revolution\Copilot\Enums\SessionSyncLevel;
use Revolution\Copilot\Enums\TaskExecutionMode;
use Revolution\Copilot\Enums\TaskShellAttachmentMode;
use Revolution\Copilot\Enums\TaskStatus;

describe('ConnectedRemoteSessionMetadataKind', function () {
    it('has coding-agent case', function () {
        expect(ConnectedRemoteSessionMetadataKind::CodingAgent->value)->toBe('coding-agent');
    });

    it('has remote-session case', function () {
        expect(ConnectedRemoteSessionMetadataKind::RemoteSession->value)->toBe('remote-session');
    });

    it('can create from string', function () {
        expect(ConnectedRemoteSessionMetadataKind::from('coding-agent'))->toBe(ConnectedRemoteSessionMetadataKind::CodingAgent)
            ->and(ConnectedRemoteSessionMetadataKind::from('remote-session'))->toBe(ConnectedRemoteSessionMetadataKind::RemoteSession);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(ConnectedRemoteSessionMetadataKind::tryFrom('unknown'))->toBeNull();
    });
});

describe('HostType', function () {
    it('has github case', function () {
        expect(HostType::GITHUB->value)->toBe('github');
    });

    it('has ado case', function () {
        expect(HostType::ADO->value)->toBe('ado');
    });

    it('can create from string', function () {
        expect(HostType::from('github'))->toBe(HostType::GITHUB)
            ->and(HostType::from('ado'))->toBe(HostType::ADO);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(HostType::tryFrom('unknown'))->toBeNull();
    });
});

describe('ModelPickerCategory', function () {
    it('has lightweight case', function () {
        expect(ModelPickerCategory::Lightweight->value)->toBe('lightweight');
    });

    it('has powerful case', function () {
        expect(ModelPickerCategory::Powerful->value)->toBe('powerful');
    });

    it('has versatile case', function () {
        expect(ModelPickerCategory::Versatile->value)->toBe('versatile');
    });

    it('can create from string', function () {
        expect(ModelPickerCategory::from('lightweight'))->toBe(ModelPickerCategory::Lightweight)
            ->and(ModelPickerCategory::from('powerful'))->toBe(ModelPickerCategory::Powerful)
            ->and(ModelPickerCategory::from('versatile'))->toBe(ModelPickerCategory::Versatile);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(ModelPickerCategory::tryFrom('unknown'))->toBeNull();
    });
});

describe('ModelPickerPriceCategory', function () {
    it('has high case', function () {
        expect(ModelPickerPriceCategory::High->value)->toBe('high');
    });

    it('has low case', function () {
        expect(ModelPickerPriceCategory::Low->value)->toBe('low');
    });

    it('has medium case', function () {
        expect(ModelPickerPriceCategory::Medium->value)->toBe('medium');
    });

    it('has very_high case', function () {
        expect(ModelPickerPriceCategory::VeryHigh->value)->toBe('very_high');
    });

    it('can create all cases from string', function () {
        expect(ModelPickerPriceCategory::from('high'))->toBe(ModelPickerPriceCategory::High)
            ->and(ModelPickerPriceCategory::from('low'))->toBe(ModelPickerPriceCategory::Low)
            ->and(ModelPickerPriceCategory::from('medium'))->toBe(ModelPickerPriceCategory::Medium)
            ->and(ModelPickerPriceCategory::from('very_high'))->toBe(ModelPickerPriceCategory::VeryHigh);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(ModelPickerPriceCategory::tryFrom('unknown'))->toBeNull();
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

    it('can create from string', function () {
        expect(SessionSyncLevel::from('local'))->toBe(SessionSyncLevel::LOCAL)
            ->and(SessionSyncLevel::from('user'))->toBe(SessionSyncLevel::USER)
            ->and(SessionSyncLevel::from('repo_and_user'))->toBe(SessionSyncLevel::REPO_AND_USER);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(SessionSyncLevel::tryFrom('unknown'))->toBeNull();
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
