<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunFailureKind;

describe('FactoryRunFailureKind', function () {
    it('has maxTotalSubagents and timeout cases', function () {
        expect(FactoryRunFailureKind::MAX_TOTAL_SUBAGENTS->value)->toBe('maxTotalSubagents')
            ->and(FactoryRunFailureKind::TIMEOUT->value)->toBe('timeout');
    });

    it('can create from string', function () {
        expect(FactoryRunFailureKind::from('maxTotalSubagents'))->toBe(FactoryRunFailureKind::MAX_TOTAL_SUBAGENTS)
            ->and(FactoryRunFailureKind::from('timeout'))->toBe(FactoryRunFailureKind::TIMEOUT);
    });
});
