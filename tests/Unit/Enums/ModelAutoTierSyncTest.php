<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ModelChangeSource;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Enums\TaskKind;

describe('TaskKind', function () {
    it('has correct values', function () {
        expect(TaskKind::AGENT->value)->toBe('agent')
            ->and(TaskKind::CLIENT->value)->toBe('client')
            ->and(TaskKind::SHELL->value)->toBe('shell');
    });
});

describe('ModelSwitchAutoTierStatus', function () {
    it('has correct values', function () {
        expect(ModelSwitchAutoTierStatus::PENDING->value)->toBe('pending')
            ->and(ModelSwitchAutoTierStatus::UNCHANGED->value)->toBe('unchanged');
    });
});

describe('ModelChangeSource', function () {
    it('has correct values', function () {
        expect(ModelChangeSource::MODEL_COMMAND->value)->toBe('model_command')
            ->and(ModelChangeSource::SETTINGS_COMMAND->value)->toBe('settings_command')
            ->and(ModelChangeSource::CONFIG_COMMAND->value)->toBe('config_command')
            ->and(ModelChangeSource::MODEL_PICKER->value)->toBe('model_picker')
            ->and(ModelChangeSource::MANAGED_SETTINGS->value)->toBe('managed_settings')
            ->and(ModelChangeSource::REPO_SETTINGS->value)->toBe('repo_settings')
            ->and(ModelChangeSource::STARTUP->value)->toBe('startup')
            ->and(ModelChangeSource::AGENT->value)->toBe('agent')
            ->and(ModelChangeSource::PLAN_MODE->value)->toBe('plan_mode')
            ->and(ModelChangeSource::AUTOMATIC->value)->toBe('automatic');
    });
});

describe('SessionEventType new cases', function () {
    it('has auto tier and completion receipt events', function () {
        expect(SessionEventType::SESSION_AUTO_TIER_SWITCH_FAILED->value)->toBe('session.auto_tier_switch_failed')
            ->and(SessionEventType::SESSION_COMPLETION_RECEIPT->value)->toBe('session.completion_receipt')
            ->and(SessionEventType::SESSION_MCP_SERVER_REMOVED->value)->toBe('session.mcp_server_removed')
            ->and(SessionEventType::SESSION_MCP_SERVER_NEEDS_RECONNECT->value)->toBe('session.mcp_server_needs_reconnect')
            ->and(SessionEventType::ASSISTANT_FUSION_PHASE_ACTIVITY->value)->toBe('assistant.fusion_phase_activity');
    });
});
