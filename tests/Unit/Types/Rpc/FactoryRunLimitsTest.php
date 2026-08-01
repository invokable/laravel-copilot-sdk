<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryRunLimits;

describe('FactoryRunLimits', function () {
    it('can be created from array', function () {
        $limits = FactoryRunLimits::fromArray([
            'maxConcurrentSubagents' => 2,
            'maxTotalSubagents' => 10,
            'timeoutSeconds' => 60.0,
            'maxAiCredits' => 5.0,
        ]);

        expect($limits->maxConcurrentSubagents)->toBe(2)
            ->and($limits->maxTotalSubagents)->toBe(10)
            ->and($limits->timeoutSeconds)->toBe(60.0)
            ->and($limits->maxAiCredits)->toBe(5.0);
    });

    it('defaults to null values', function () {
        $limits = FactoryRunLimits::fromArray([]);

        expect($limits->maxConcurrentSubagents)->toBeNull()
            ->and($limits->maxTotalSubagents)->toBeNull()
            ->and($limits->timeoutSeconds)->toBeNull()
            ->and($limits->maxAiCredits)->toBeNull();
    });

    it('converts to array correctly', function () {
        $limits = new FactoryRunLimits(maxConcurrentSubagents: 2, maxTotalSubagents: 10, timeoutSeconds: 60.0, maxAiCredits: 5.0);

        expect($limits->toArray())->toBe([
            'maxConcurrentSubagents' => 2,
            'maxTotalSubagents' => 10,
            'timeoutSeconds' => 60.0,
            'maxAiCredits' => 5.0,
        ]);
    });
});
