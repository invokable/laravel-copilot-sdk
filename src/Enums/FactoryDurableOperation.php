<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Execution-critical factory storage operation.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum FactoryDurableOperation: string
{
    case CREATE_RUN = 'createRun';
    case MARK_RUN_STARTED = 'markRunStarted';
    case FINISH_RUN = 'finishRun';
    case RESERVE_AGENT = 'reserveAgent';
    case RELEASE_AGENT = 'releaseAgent';
    case CHARGE_CREDIT = 'chargeCredit';
    case ADD_ELAPSED = 'addElapsed';
    case RECONCILE_CREDIT_TOTAL = 'reconcileCreditTotal';
    case JOURNAL_GET = 'journalGet';
    case JOURNAL_PUT = 'journalPut';
}
