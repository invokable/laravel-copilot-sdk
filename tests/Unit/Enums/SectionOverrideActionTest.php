<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SectionOverrideAction;

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
