<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutoModeResolvedReasoningBucket;
use Revolution\Copilot\Enums\SessionEventType;

describe('AutoModeResolvedReasoningBucket', function () {
    it('has correct values', function () {
        expect(AutoModeResolvedReasoningBucket::Low->value)->toBe('low')
            ->and(AutoModeResolvedReasoningBucket::Medium->value)->toBe('medium')
            ->and(AutoModeResolvedReasoningBucket::High->value)->toBe('high');
    });

    it('can be created from value', function () {
        expect(AutoModeResolvedReasoningBucket::from('high'))->toBe(AutoModeResolvedReasoningBucket::High);
    });
});

describe('SessionEventType new cases', function () {
    it('has auto_mode_resolved event', function () {
        expect(SessionEventType::AUTO_MODE_RESOLVED->value)->toBe('session.auto_mode_resolved');
    });

    it('has mcp list changed events', function () {
        expect(SessionEventType::MCP_TOOLS_LIST_CHANGED->value)->toBe('mcp.tools.list_changed')
            ->and(SessionEventType::MCP_RESOURCES_LIST_CHANGED->value)->toBe('mcp.resources.list_changed')
            ->and(SessionEventType::MCP_PROMPTS_LIST_CHANGED->value)->toBe('mcp.prompts.list_changed');
    });
});
