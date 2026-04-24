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
});
