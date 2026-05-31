<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ContextTier;

describe('ContextTier', function () {
    it('has correct string values', function () {
        expect(ContextTier::DEFAULT->value)->toBe('default')
            ->and(ContextTier::LONG_CONTEXT->value)->toBe('long_context');
    });

    it('can be created from string', function () {
        expect(ContextTier::from('default'))->toBe(ContextTier::DEFAULT)
            ->and(ContextTier::from('long_context'))->toBe(ContextTier::LONG_CONTEXT);
    });

    it('has all expected cases', function () {
        $cases = ContextTier::cases();

        expect($cases)->toHaveCount(2)
            ->and($cases)->toContain(ContextTier::DEFAULT)
            ->and($cases)->toContain(ContextTier::LONG_CONTEXT);
    });
});
