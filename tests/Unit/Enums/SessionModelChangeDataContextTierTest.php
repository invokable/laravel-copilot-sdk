<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionModelChangeDataContextTier;

describe('SessionModelChangeDataContextTier', function () {
    it('has correct string values', function () {
        expect(SessionModelChangeDataContextTier::DEFAULT->value)->toBe('default')
            ->and(SessionModelChangeDataContextTier::LONG_CONTEXT->value)->toBe('long_context');
    });

    it('can be created from string', function () {
        expect(SessionModelChangeDataContextTier::from('default'))->toBe(SessionModelChangeDataContextTier::DEFAULT)
            ->and(SessionModelChangeDataContextTier::from('long_context'))->toBe(SessionModelChangeDataContextTier::LONG_CONTEXT);
    });

    it('has all expected cases', function () {
        $cases = SessionModelChangeDataContextTier::cases();

        expect($cases)->toHaveCount(2)
            ->and($cases)->toContain(SessionModelChangeDataContextTier::DEFAULT)
            ->and($cases)->toContain(SessionModelChangeDataContextTier::LONG_CONTEXT);
    });
});
