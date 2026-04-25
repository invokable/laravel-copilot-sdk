<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpTransportType;

describe('McpTransportType', function () {
    it('has stdio case', function () {
        expect(McpTransportType::STDIO->value)->toBe('stdio');
    });

    it('has http case', function () {
        expect(McpTransportType::HTTP->value)->toBe('http');
    });

    it('has sse case', function () {
        expect(McpTransportType::SSE->value)->toBe('sse');
    });

    it('has memory case', function () {
        expect(McpTransportType::MEMORY->value)->toBe('memory');
    });

    it('can be created from string', function () {
        expect(McpTransportType::from('stdio'))->toBe(McpTransportType::STDIO)
            ->and(McpTransportType::from('http'))->toBe(McpTransportType::HTTP)
            ->and(McpTransportType::from('sse'))->toBe(McpTransportType::SSE)
            ->and(McpTransportType::from('memory'))->toBe(McpTransportType::MEMORY);
    });

    it('returns null for invalid value via tryFrom', function () {
        expect(McpTransportType::tryFrom('invalid'))->toBeNull()
            ->and(McpTransportType::tryFrom('ws'))->toBeNull();
    });

    it('has exactly four cases', function () {
        expect(McpTransportType::cases())->toHaveCount(4);
    });
});
