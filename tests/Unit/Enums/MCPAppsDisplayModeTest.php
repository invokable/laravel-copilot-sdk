<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\MCPAppsDisplayMode;

describe('MCPAppsDisplayMode', function () {
    it('has correct string values', function () {
        expect(MCPAppsDisplayMode::FULLSCREEN->value)->toBe('fullscreen')
            ->and(MCPAppsDisplayMode::INLINE->value)->toBe('inline')
            ->and(MCPAppsDisplayMode::PIP->value)->toBe('pip');
    });

    it('can be created from string', function () {
        expect(MCPAppsDisplayMode::from('fullscreen'))->toBe(MCPAppsDisplayMode::FULLSCREEN)
            ->and(MCPAppsDisplayMode::from('inline'))->toBe(MCPAppsDisplayMode::INLINE)
            ->and(MCPAppsDisplayMode::from('pip'))->toBe(MCPAppsDisplayMode::PIP);
    });

    it('has all expected cases', function () {
        $cases = MCPAppsDisplayMode::cases();

        expect($cases)->toHaveCount(3)
            ->and($cases)->toContain(MCPAppsDisplayMode::FULLSCREEN)
            ->and($cases)->toContain(MCPAppsDisplayMode::INLINE)
            ->and($cases)->toContain(MCPAppsDisplayMode::PIP);
    });
});
