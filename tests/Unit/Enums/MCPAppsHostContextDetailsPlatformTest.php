<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\MCPAppsHostContextDetailsPlatform;

describe('MCPAppsHostContextDetailsPlatform', function () {
    it('has correct string values', function () {
        expect(MCPAppsHostContextDetailsPlatform::DESKTOP->value)->toBe('desktop')
            ->and(MCPAppsHostContextDetailsPlatform::MOBILE->value)->toBe('mobile')
            ->and(MCPAppsHostContextDetailsPlatform::WEB->value)->toBe('web');
    });

    it('can be created from string', function () {
        expect(MCPAppsHostContextDetailsPlatform::from('desktop'))->toBe(MCPAppsHostContextDetailsPlatform::DESKTOP)
            ->and(MCPAppsHostContextDetailsPlatform::from('mobile'))->toBe(MCPAppsHostContextDetailsPlatform::MOBILE)
            ->and(MCPAppsHostContextDetailsPlatform::from('web'))->toBe(MCPAppsHostContextDetailsPlatform::WEB);
    });

    it('has all expected cases', function () {
        $cases = MCPAppsHostContextDetailsPlatform::cases();

        expect($cases)->toHaveCount(3)
            ->and($cases)->toContain(MCPAppsHostContextDetailsPlatform::DESKTOP)
            ->and($cases)->toContain(MCPAppsHostContextDetailsPlatform::MOBILE)
            ->and($cases)->toContain(MCPAppsHostContextDetailsPlatform::WEB);
    });
});
