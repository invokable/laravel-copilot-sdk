<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Enums\ExtensionSource;
use Revolution\Copilot\Enums\ExtensionStatus;
use Revolution\Copilot\Enums\McpServerStatus;
use Revolution\Copilot\Enums\SectionOverrideAction;

describe('McpServerStatus', function () {
    it('has correct string values', function () {
        expect(McpServerStatus::CONNECTED->value)->toBe('connected')
            ->and(McpServerStatus::FAILED->value)->toBe('failed')
            ->and(McpServerStatus::PENDING->value)->toBe('pending')
            ->and(McpServerStatus::DISABLED->value)->toBe('disabled')
            ->and(McpServerStatus::NOT_CONFIGURED->value)->toBe('not_configured');
    });

    it('can be created from string', function () {
        expect(McpServerStatus::from('connected'))->toBe(McpServerStatus::CONNECTED)
            ->and(McpServerStatus::from('failed'))->toBe(McpServerStatus::FAILED)
            ->and(McpServerStatus::from('pending'))->toBe(McpServerStatus::PENDING)
            ->and(McpServerStatus::from('disabled'))->toBe(McpServerStatus::DISABLED)
            ->and(McpServerStatus::from('not_configured'))->toBe(McpServerStatus::NOT_CONFIGURED);
    });

    it('has all expected cases', function () {
        expect(McpServerStatus::cases())->toHaveCount(5);
    });
});

describe('ExtensionSource', function () {
    it('has correct string values', function () {
        expect(ExtensionSource::PROJECT->value)->toBe('project')
            ->and(ExtensionSource::USER->value)->toBe('user');
    });

    it('can be created from string', function () {
        expect(ExtensionSource::from('project'))->toBe(ExtensionSource::PROJECT)
            ->and(ExtensionSource::from('user'))->toBe(ExtensionSource::USER);
    });

    it('has all expected cases', function () {
        expect(ExtensionSource::cases())->toHaveCount(2);
    });
});

describe('ExtensionStatus', function () {
    it('has correct string values', function () {
        expect(ExtensionStatus::RUNNING->value)->toBe('running')
            ->and(ExtensionStatus::DISABLED->value)->toBe('disabled')
            ->and(ExtensionStatus::FAILED->value)->toBe('failed')
            ->and(ExtensionStatus::STARTING->value)->toBe('starting');
    });

    it('can be created from string', function () {
        expect(ExtensionStatus::from('running'))->toBe(ExtensionStatus::RUNNING)
            ->and(ExtensionStatus::from('disabled'))->toBe(ExtensionStatus::DISABLED)
            ->and(ExtensionStatus::from('failed'))->toBe(ExtensionStatus::FAILED)
            ->and(ExtensionStatus::from('starting'))->toBe(ExtensionStatus::STARTING);
    });

    it('has all expected cases', function () {
        expect(ExtensionStatus::cases())->toHaveCount(4);
    });
});

describe('ElicitationAction', function () {
    it('has correct string values', function () {
        expect(ElicitationAction::ACCEPT->value)->toBe('accept')
            ->and(ElicitationAction::DECLINE->value)->toBe('decline')
            ->and(ElicitationAction::CANCEL->value)->toBe('cancel');
    });

    it('can be created from string', function () {
        expect(ElicitationAction::from('accept'))->toBe(ElicitationAction::ACCEPT)
            ->and(ElicitationAction::from('decline'))->toBe(ElicitationAction::DECLINE)
            ->and(ElicitationAction::from('cancel'))->toBe(ElicitationAction::CANCEL);
    });

    it('has all expected cases', function () {
        expect(ElicitationAction::cases())->toHaveCount(3);
    });
});

describe('SectionOverrideAction', function () {
    it('has correct string values', function () {
        expect(SectionOverrideAction::REPLACE->value)->toBe('replace')
            ->and(SectionOverrideAction::REMOVE->value)->toBe('remove')
            ->and(SectionOverrideAction::APPEND->value)->toBe('append')
            ->and(SectionOverrideAction::PREPEND->value)->toBe('prepend')
            ->and(SectionOverrideAction::TRANSFORM->value)->toBe('transform');
    });

    it('can be created from string', function () {
        expect(SectionOverrideAction::from('replace'))->toBe(SectionOverrideAction::REPLACE)
            ->and(SectionOverrideAction::from('remove'))->toBe(SectionOverrideAction::REMOVE)
            ->and(SectionOverrideAction::from('append'))->toBe(SectionOverrideAction::APPEND)
            ->and(SectionOverrideAction::from('prepend'))->toBe(SectionOverrideAction::PREPEND)
            ->and(SectionOverrideAction::from('transform'))->toBe(SectionOverrideAction::TRANSFORM);
    });

    it('has all expected cases', function () {
        expect(SectionOverrideAction::cases())->toHaveCount(5);
    });
});
