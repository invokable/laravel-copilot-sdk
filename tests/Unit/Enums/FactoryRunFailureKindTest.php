<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunFailureKind;

describe('FactoryRunFailureKind', function () {
    it('has maxTotalSubagents, timeoutSeconds, and maxAiCredits cases', function () {
        expect(FactoryRunFailureKind::MAX_TOTAL_SUBAGENTS->value)->toBe('maxTotalSubagents')
            ->and(FactoryRunFailureKind::TIMEOUT->value)->toBe('timeoutSeconds')
            ->and(FactoryRunFailureKind::MAX_AI_CREDITS->value)->toBe('maxAiCredits');
    });

    it('can create from string', function () {
        expect(FactoryRunFailureKind::from('maxTotalSubagents'))->toBe(FactoryRunFailureKind::MAX_TOTAL_SUBAGENTS)
            ->and(FactoryRunFailureKind::from('timeoutSeconds'))->toBe(FactoryRunFailureKind::TIMEOUT)
            ->and(FactoryRunFailureKind::from('maxAiCredits'))->toBe(FactoryRunFailureKind::MAX_AI_CREDITS);
    });
});
