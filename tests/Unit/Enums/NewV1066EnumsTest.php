<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Enums\SessionVisibilityStatus;

describe('New SessionEventType cases', function () {
    it('has session.response_limits_changed case', function () {
        expect(SessionEventType::SESSION_RESPONSE_LIMITS_CHANGED->value)->toBe('session.response_limits_changed');
    });

    it('has assistant.idle case', function () {
        expect(SessionEventType::ASSISTANT_IDLE->value)->toBe('assistant.idle');
    });

    it('has mcp.headers_refresh_required case', function () {
        expect(SessionEventType::MCP_HEADERS_REFRESH_REQUIRED->value)->toBe('mcp.headers_refresh_required');
    });

    it('has mcp.headers_refresh_completed case', function () {
        expect(SessionEventType::MCP_HEADERS_REFRESH_COMPLETED->value)->toBe('mcp.headers_refresh_completed');
    });

    it('can create from string', function () {
        expect(SessionEventType::from('session.response_limits_changed'))->toBe(SessionEventType::SESSION_RESPONSE_LIMITS_CHANGED)
            ->and(SessionEventType::from('assistant.idle'))->toBe(SessionEventType::ASSISTANT_IDLE)
            ->and(SessionEventType::from('mcp.headers_refresh_required'))->toBe(SessionEventType::MCP_HEADERS_REFRESH_REQUIRED)
            ->and(SessionEventType::from('mcp.headers_refresh_completed'))->toBe(SessionEventType::MCP_HEADERS_REFRESH_COMPLETED);
    });
});

describe('SessionVisibilityStatus', function () {
    it('has repo case', function () {
        expect(SessionVisibilityStatus::REPO->value)->toBe('repo');
    });

    it('has unshared case', function () {
        expect(SessionVisibilityStatus::UNSHARED->value)->toBe('unshared');
    });

    it('can create from string', function () {
        expect(SessionVisibilityStatus::from('repo'))->toBe(SessionVisibilityStatus::REPO)
            ->and(SessionVisibilityStatus::from('unshared'))->toBe(SessionVisibilityStatus::UNSHARED);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(SessionVisibilityStatus::tryFrom('unknown'))->toBeNull();
    });
});
