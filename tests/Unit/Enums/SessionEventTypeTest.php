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
});
