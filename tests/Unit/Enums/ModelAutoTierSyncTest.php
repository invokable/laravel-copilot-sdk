<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ModelChangeSource;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;

describe('ModelSwitchAutoTierStatus', function () {
    it('has correct values', function () {
        expect(ModelSwitchAutoTierStatus::PENDING->value)->toBe('pending')
            ->and(ModelSwitchAutoTierStatus::UNCHANGED->value)->toBe('unchanged');
    });
});

describe('ModelChangeSource', function () {
    it('has correct values', function () {
        expect(ModelChangeSource::MODEL_COMMAND->value)->toBe('model_command')
            ->and(ModelChangeSource::SETTINGS_COMMAND->value)->toBe('settings_command')
            ->and(ModelChangeSource::CONFIG_COMMAND->value)->toBe('config_command')
            ->and(ModelChangeSource::MODEL_PICKER->value)->toBe('model_picker')
            ->and(ModelChangeSource::MANAGED_SETTINGS->value)->toBe('managed_settings')
            ->and(ModelChangeSource::REPO_SETTINGS->value)->toBe('repo_settings')
            ->and(ModelChangeSource::STARTUP->value)->toBe('startup')
            ->and(ModelChangeSource::AGENT->value)->toBe('agent')
            ->and(ModelChangeSource::PLAN_MODE->value)->toBe('plan_mode')
            ->and(ModelChangeSource::AUTOMATIC->value)->toBe('automatic');
    });
});
