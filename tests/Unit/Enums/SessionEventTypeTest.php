<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionEventType;

describe('SessionEventType', function () {
    it('has MCP OAuth event types', function () {
        expect(SessionEventType::MCP_OAUTH_REQUIRED->value)->toBe('mcp.oauth_required')
            ->and(SessionEventType::MCP_OAUTH_COMPLETED->value)->toBe('mcp.oauth_completed');
    });

    it('can create MCP OAuth types from string', function () {
        expect(SessionEventType::from('mcp.oauth_required'))->toBe(SessionEventType::MCP_OAUTH_REQUIRED)
            ->and(SessionEventType::from('mcp.oauth_completed'))->toBe(SessionEventType::MCP_OAUTH_COMPLETED);
    });

    it('has system message and notification types', function () {
        expect(SessionEventType::SYSTEM_MESSAGE->value)->toBe('system.message')
            ->and(SessionEventType::SYSTEM_NOTIFICATION->value)->toBe('system.notification');
    });

    it('can create system types from string', function () {
        expect(SessionEventType::from('system.message'))->toBe(SessionEventType::SYSTEM_MESSAGE)
            ->and(SessionEventType::from('system.notification'))->toBe(SessionEventType::SYSTEM_NOTIFICATION);
    });

    it('has custom agents updated event type', function () {
        expect(SessionEventType::SESSION_CUSTOM_AGENTS_UPDATED->value)->toBe('session.custom_agents_updated');
    });

    it('can create custom agents updated from string', function () {
        expect(SessionEventType::from('session.custom_agents_updated'))->toBe(SessionEventType::SESSION_CUSTOM_AGENTS_UPDATED);
    });

    it('has loaded event types for extensions, MCP servers, and skills', function () {
        expect(SessionEventType::SESSION_EXTENSIONS_LOADED->value)->toBe('session.extensions_loaded')
            ->and(SessionEventType::SESSION_MCP_SERVERS_LOADED->value)->toBe('session.mcp_servers_loaded')
            ->and(SessionEventType::SESSION_MCP_SERVER_STATUS_CHANGED->value)->toBe('session.mcp_server_status_changed')
            ->and(SessionEventType::SESSION_SKILLS_LOADED->value)->toBe('session.skills_loaded');
    });

    it('can create loaded event types from string', function () {
        expect(SessionEventType::from('session.extensions_loaded'))->toBe(SessionEventType::SESSION_EXTENSIONS_LOADED)
            ->and(SessionEventType::from('session.mcp_servers_loaded'))->toBe(SessionEventType::SESSION_MCP_SERVERS_LOADED)
            ->and(SessionEventType::from('session.mcp_server_status_changed'))->toBe(SessionEventType::SESSION_MCP_SERVER_STATUS_CHANGED)
            ->and(SessionEventType::from('session.skills_loaded'))->toBe(SessionEventType::SESSION_SKILLS_LOADED);
    });

    it('has capabilities changed event type', function () {
        expect(SessionEventType::CAPABILITIES_CHANGED->value)->toBe('capabilities.changed');
    });

    it('can create capabilities changed from string', function () {
        expect(SessionEventType::from('capabilities.changed'))->toBe(SessionEventType::CAPABILITIES_CHANGED);
    });

    it('has sampling event types', function () {
        expect(SessionEventType::SAMPLING_REQUESTED->value)->toBe('sampling.requested')
            ->and(SessionEventType::SAMPLING_COMPLETED->value)->toBe('sampling.completed');
    });

    it('can create sampling event types from string', function () {
        expect(SessionEventType::from('sampling.requested'))->toBe(SessionEventType::SAMPLING_REQUESTED)
            ->and(SessionEventType::from('sampling.completed'))->toBe(SessionEventType::SAMPLING_COMPLETED);
    });

    it('has remote steerable changed event type', function () {
        expect(SessionEventType::SESSION_REMOTE_STEERABLE_CHANGED->value)->toBe('session.remote_steerable_changed');
    });

    it('can create remote steerable changed from string', function () {
        expect(SessionEventType::from('session.remote_steerable_changed'))->toBe(SessionEventType::SESSION_REMOTE_STEERABLE_CHANGED);
    });

    it('has auto mode switch event types', function () {
        expect(SessionEventType::AUTO_MODE_SWITCH_REQUESTED->value)->toBe('auto_mode_switch.requested')
            ->and(SessionEventType::AUTO_MODE_SWITCH_COMPLETED->value)->toBe('auto_mode_switch.completed');
    });

    it('can create auto mode switch event types from string', function () {
        expect(SessionEventType::from('auto_mode_switch.requested'))->toBe(SessionEventType::AUTO_MODE_SWITCH_REQUESTED)
            ->and(SessionEventType::from('auto_mode_switch.completed'))->toBe(SessionEventType::AUTO_MODE_SWITCH_COMPLETED);
    });

    it('has session limits changed event type', function () {
        expect(SessionEventType::SESSION_SESSION_LIMITS_CHANGED->value)->toBe('session.session_limits_changed');
    });

    it('can create session limits changed from string', function () {
        expect(SessionEventType::from('session.session_limits_changed'))->toBe(SessionEventType::SESSION_SESSION_LIMITS_CHANGED);
    });

    it('has usage checkpoint event type', function () {
        expect(SessionEventType::SESSION_USAGE_CHECKPOINT->value)->toBe('session.usage_checkpoint');
    });

    it('can create usage checkpoint from string', function () {
        expect(SessionEventType::from('session.usage_checkpoint'))->toBe(SessionEventType::SESSION_USAGE_CHECKPOINT);
    });

    it('has session limits exhausted event types', function () {
        expect(SessionEventType::SESSION_LIMITS_EXHAUSTED_REQUESTED->value)->toBe('session_limits_exhausted.requested')
            ->and(SessionEventType::SESSION_LIMITS_EXHAUSTED_COMPLETED->value)->toBe('session_limits_exhausted.completed');
    });

    it('can create session limits exhausted event types from string', function () {
        expect(SessionEventType::from('session_limits_exhausted.requested'))->toBe(SessionEventType::SESSION_LIMITS_EXHAUSTED_REQUESTED)
            ->and(SessionEventType::from('session_limits_exhausted.completed'))->toBe(SessionEventType::SESSION_LIMITS_EXHAUSTED_COMPLETED);
    });

    it('has assistant message start event type', function () {
        expect(SessionEventType::ASSISTANT_MESSAGE_START->value)->toBe('assistant.message_start');
    });

    it('can create assistant message start from string', function () {
        expect(SessionEventType::from('assistant.message_start'))->toBe(SessionEventType::ASSISTANT_MESSAGE_START);
    });

    it('has schedule event types', function () {
        expect(SessionEventType::SESSION_SCHEDULE_CREATED->value)->toBe('session.schedule_created')
            ->and(SessionEventType::SESSION_SCHEDULE_CANCELLED->value)->toBe('session.schedule_cancelled');
    });

    it('can create schedule event types from string', function () {
        expect(SessionEventType::from('session.schedule_created'))->toBe(SessionEventType::SESSION_SCHEDULE_CREATED)
            ->and(SessionEventType::from('session.schedule_cancelled'))->toBe(SessionEventType::SESSION_SCHEDULE_CANCELLED);
    });

    it('has canvas closed event type', function () {
        expect(SessionEventType::SESSION_CANVAS_CLOSED->value)->toBe('session.canvas.closed');
    });

    it('can create canvas closed event type from string', function () {
        expect(SessionEventType::from('session.canvas.closed'))->toBe(SessionEventType::SESSION_CANVAS_CLOSED);
    });

    it('has todos changed and binary asset event types', function () {
        expect(SessionEventType::SESSION_TODOS_CHANGED->value)->toBe('session.todos_changed')
            ->and(SessionEventType::SESSION_BINARY_ASSET->value)->toBe('session.binary_asset');
    });

    it('can create todos changed and binary asset from string', function () {
        expect(SessionEventType::from('session.todos_changed'))->toBe(SessionEventType::SESSION_TODOS_CHANGED)
            ->and(SessionEventType::from('session.binary_asset'))->toBe(SessionEventType::SESSION_BINARY_ASSET);
    });

    it('has assistant server tool progress event type', function () {
        expect(SessionEventType::ASSISTANT_SERVER_TOOL_PROGRESS->value)->toBe('assistant.server_tool_progress');
    });

    it('can create assistant server tool progress from string', function () {
        expect(SessionEventType::from('assistant.server_tool_progress'))->toBe(SessionEventType::ASSISTANT_SERVER_TOOL_PROGRESS);
    });

    it('has managed settings resolved event type', function () {
        expect(SessionEventType::MANAGED_SETTINGS_RESOLVED->value)->toBe('session.managed_settings_resolved');
    });

    it('can create managed settings resolved from string', function () {
        expect(SessionEventType::from('session.managed_settings_resolved'))->toBe(SessionEventType::MANAGED_SETTINGS_RESOLVED);
    });
});
