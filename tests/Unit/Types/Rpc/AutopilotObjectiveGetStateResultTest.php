<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutopilotObjectiveStatus;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveCreditLimit;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveGetStateResult;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveState;

describe('AutopilotObjectiveGetStateResult', function () {
    it('preserves a null state when converting to an array', function () {
        $result = AutopilotObjectiveGetStateResult::fromArray(['state' => null]);

        expect($result->state)->toBeNull()
            ->and($result->toArray())->toBe(['state' => null]);
    });

    it('hydrates and round-trips the objective state and nested credit limit', function () {
        $data = [
            'state' => [
                'id' => 12,
                'objective' => 'Finish the task',
                'status' => 'active',
                'turnCount' => 4,
                'pauseReason' => 'Waiting for user input',
                'completionSummary' => 'Completed the requested changes',
                'creditCountNanoAiu' => '3750000000',
                'creditLimit' => [
                    'credits' => 12.5,
                    'creditsUsed' => 3.75,
                    'creditsUsedNanoAiu' => '3750000000',
                ],
            ],
        ];

        $result = AutopilotObjectiveGetStateResult::fromArray($data);

        expect($result->state)->toBeInstanceOf(AutopilotObjectiveState::class)
            ->and($result->state->status)->toBe(AutopilotObjectiveStatus::ACTIVE)
            ->and($result->state->creditLimit)->toBeInstanceOf(AutopilotObjectiveCreditLimit::class)
            ->and($result->state->creditLimit->creditsUsedNanoAiu)->toBe('3750000000')
            ->and($result->toArray())->toBe($data);
    });
});
