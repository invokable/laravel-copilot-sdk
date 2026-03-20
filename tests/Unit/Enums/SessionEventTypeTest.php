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
});
