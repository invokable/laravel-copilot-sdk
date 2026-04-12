<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ElicitationAction;

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
