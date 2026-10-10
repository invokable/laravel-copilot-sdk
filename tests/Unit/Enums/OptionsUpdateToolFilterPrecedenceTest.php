<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\OptionsUpdateToolFilterPrecedence;

describe('OptionsUpdateToolFilterPrecedence', function () {
    it('has the official tool filter values', function () {
        expect(OptionsUpdateToolFilterPrecedence::Available->value)->toBe('available')
            ->and(OptionsUpdateToolFilterPrecedence::Excluded->value)->toBe('excluded');
    });
});
