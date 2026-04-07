<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EntryType;

describe('EntryType', function () {
    it('has correct string values', function () {
        expect(EntryType::File->value)->toBe('file')
            ->and(EntryType::Directory->value)->toBe('directory');
    });

    it('can be created from string', function () {
        expect(EntryType::from('file'))->toBe(EntryType::File)
            ->and(EntryType::from('directory'))->toBe(EntryType::Directory);
    });

    it('returns null for unknown values with tryFrom', function () {
        expect(EntryType::tryFrom('symlink'))->toBeNull()
            ->and(EntryType::tryFrom('unknown'))->toBeNull();
    });

    it('has all expected cases', function () {
        expect(EntryType::cases())->toHaveCount(2);
    });
});
