<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunStatus;

describe('FactoryRunStatus', function () {
    it('has all expected cases', function () {
        expect(FactoryRunStatus::PENDING->value)->toBe('pending')
            ->and(FactoryRunStatus::RUNNING->value)->toBe('running')
            ->and(FactoryRunStatus::COMPLETED->value)->toBe('completed')
            ->and(FactoryRunStatus::HALTED->value)->toBe('halted')
            ->and(FactoryRunStatus::CANCELLED->value)->toBe('cancelled')
            ->and(FactoryRunStatus::ERROR->value)->toBe('error');
    });

    it('can create from string', function () {
        expect(FactoryRunStatus::from('pending'))->toBe(FactoryRunStatus::PENDING)
            ->and(FactoryRunStatus::from('error'))->toBe(FactoryRunStatus::ERROR);
    });
});
