<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpServerStatus;

describe('McpServerStatus', function () {
    it('has correct string values', function () {
        expect(McpServerStatus::CONNECTED->value)->toBe('connected')
            ->and(McpServerStatus::FAILED->value)->toBe('failed')
            ->and(McpServerStatus::NEEDS_AUTH->value)->toBe('needs-auth')
            ->and(McpServerStatus::PENDING->value)->toBe('pending')
            ->and(McpServerStatus::DISABLED->value)->toBe('disabled')
            ->and(McpServerStatus::NOT_CONFIGURED->value)->toBe('not_configured');
    });

    it('can be created from string', function () {
        expect(McpServerStatus::from('connected'))->toBe(McpServerStatus::CONNECTED)
            ->and(McpServerStatus::from('failed'))->toBe(McpServerStatus::FAILED)
            ->and(McpServerStatus::from('needs-auth'))->toBe(McpServerStatus::NEEDS_AUTH)
            ->and(McpServerStatus::from('pending'))->toBe(McpServerStatus::PENDING)
            ->and(McpServerStatus::from('disabled'))->toBe(McpServerStatus::DISABLED)
            ->and(McpServerStatus::from('not_configured'))->toBe(McpServerStatus::NOT_CONFIGURED);
    });

    it('has all expected cases', function () {
        expect(McpServerStatus::cases())->toHaveCount(6);
    });
});
