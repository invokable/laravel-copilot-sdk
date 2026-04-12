<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AttachmentType;

describe('AttachmentType', function () {
    it('has correct string values', function () {
        expect(AttachmentType::DIRECTORY->value)->toBe('directory')
            ->and(AttachmentType::FILE->value)->toBe('file')
            ->and(AttachmentType::GITHUB_REFERENCE->value)->toBe('github_reference')
            ->and(AttachmentType::SELECTION->value)->toBe('selection')
            ->and(AttachmentType::BLOB->value)->toBe('blob');
    });

    it('can be created from string', function () {
        expect(AttachmentType::from('directory'))->toBe(AttachmentType::DIRECTORY)
            ->and(AttachmentType::from('file'))->toBe(AttachmentType::FILE)
            ->and(AttachmentType::from('github_reference'))->toBe(AttachmentType::GITHUB_REFERENCE)
            ->and(AttachmentType::from('selection'))->toBe(AttachmentType::SELECTION)
            ->and(AttachmentType::from('blob'))->toBe(AttachmentType::BLOB);
    });

    it('has all expected cases', function () {
        expect(AttachmentType::cases())->toHaveCount(5);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(AttachmentType::tryFrom('invalid'))->toBeNull();
    });
});
