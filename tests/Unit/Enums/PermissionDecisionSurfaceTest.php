<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\PermissionDecisionSurface;

describe('PermissionDecisionSurface', function () {
    it('has correct string values', function () {
        expect(PermissionDecisionSurface::TUI->value)->toBe('tui')
            ->and(PermissionDecisionSurface::PROMPT_MODE->value)->toBe('prompt_mode')
            ->and(PermissionDecisionSurface::COPILOT_APP->value)->toBe('copilot_app')
            ->and(PermissionDecisionSurface::SDK->value)->toBe('sdk');
    });

    it('can be created from string', function () {
        expect(PermissionDecisionSurface::from('tui'))->toBe(PermissionDecisionSurface::TUI)
            ->and(PermissionDecisionSurface::from('prompt_mode'))->toBe(PermissionDecisionSurface::PROMPT_MODE)
            ->and(PermissionDecisionSurface::from('copilot_app'))->toBe(PermissionDecisionSurface::COPILOT_APP)
            ->and(PermissionDecisionSurface::from('sdk'))->toBe(PermissionDecisionSurface::SDK);
    });
});
