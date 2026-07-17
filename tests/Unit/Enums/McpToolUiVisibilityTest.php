<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpToolUiVisibility;

describe('McpToolUiVisibility', function () {
    it('has correct string values', function () {
        expect(McpToolUiVisibility::Model->value)->toBe('model')
            ->and(McpToolUiVisibility::App->value)->toBe('app');
    });

    it('can be created from string', function () {
        expect(McpToolUiVisibility::from('model'))->toBe(McpToolUiVisibility::Model)
            ->and(McpToolUiVisibility::from('app'))->toBe(McpToolUiVisibility::App);
    });

    it('has all expected cases', function () {
        expect(McpToolUiVisibility::cases())->toHaveCount(2);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(McpToolUiVisibility::tryFrom('invalid'))->toBeNull();
    });
});
