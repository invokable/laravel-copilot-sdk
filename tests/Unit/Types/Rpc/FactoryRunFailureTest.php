<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunFailureKind;
use Revolution\Copilot\Enums\FactoryRunFailureType;
use Revolution\Copilot\Types\Rpc\FactoryRunFailure;

describe('FactoryRunFailure', function () {
    it('can be created from array', function () {
        $failure = FactoryRunFailure::fromArray([
            'runId' => 'run-1',
            'type' => 'factory_limit_reached',
            'kind' => 'timeout',
            'value' => 60000.0,
        ]);

        expect($failure->runId)->toBe('run-1')
            ->and($failure->type)->toBe(FactoryRunFailureType::FACTORY_LIMIT_REACHED)
            ->and($failure->kind)->toBe(FactoryRunFailureKind::TIMEOUT)
            ->and($failure->value)->toBe(60000.0);
    });

    it('converts to array correctly', function () {
        $failure = new FactoryRunFailure(
            runId: 'run-1',
            type: FactoryRunFailureType::FACTORY_RESUME_DECLINED,
            reason: 'declined',
        );

        expect($failure->toArray())->toBe([
            'runId' => 'run-1',
            'type' => 'factory_resume_declined',
            'reason' => 'declined',
        ]);
    });
});
