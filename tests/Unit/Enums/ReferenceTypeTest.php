<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ReferenceType;

describe('ReferenceType', function () {
    it('has correct string values', function () {
        expect(ReferenceType::DISCUSSION->value)->toBe('discussion')
            ->and(ReferenceType::ISSUE->value)->toBe('issue')
            ->and(ReferenceType::PR->value)->toBe('pr');
    });

    it('can be created from string', function () {
        expect(ReferenceType::from('discussion'))->toBe(ReferenceType::DISCUSSION)
            ->and(ReferenceType::from('issue'))->toBe(ReferenceType::ISSUE)
            ->and(ReferenceType::from('pr'))->toBe(ReferenceType::PR);
    });

    it('has all expected cases', function () {
        expect(ReferenceType::cases())->toHaveCount(3);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(ReferenceType::tryFrom('invalid'))->toBeNull();
    });
});
