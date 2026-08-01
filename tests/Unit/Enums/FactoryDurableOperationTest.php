<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryDurableOperation;

describe('FactoryDurableOperation', function () {
    it('has all expected cases', function () {
        expect(FactoryDurableOperation::CREATE_RUN->value)->toBe('createRun')
            ->and(FactoryDurableOperation::MARK_RUN_STARTED->value)->toBe('markRunStarted')
            ->and(FactoryDurableOperation::FINISH_RUN->value)->toBe('finishRun')
            ->and(FactoryDurableOperation::RESERVE_AGENT->value)->toBe('reserveAgent')
            ->and(FactoryDurableOperation::RELEASE_AGENT->value)->toBe('releaseAgent')
            ->and(FactoryDurableOperation::CHARGE_CREDIT->value)->toBe('chargeCredit')
            ->and(FactoryDurableOperation::ADD_ELAPSED->value)->toBe('addElapsed')
            ->and(FactoryDurableOperation::RECONCILE_CREDIT_TOTAL->value)->toBe('reconcileCreditTotal')
            ->and(FactoryDurableOperation::JOURNAL_GET->value)->toBe('journalGet')
            ->and(FactoryDurableOperation::JOURNAL_PUT->value)->toBe('journalPut');
    });

    it('can create from string', function () {
        expect(FactoryDurableOperation::from('journalPut'))->toBe(FactoryDurableOperation::JOURNAL_PUT);
    });
});
