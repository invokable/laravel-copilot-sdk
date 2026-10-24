<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutopilotObjectiveStatus;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingAutopilotObjective;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveCreditLimit;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveGetStateResult;
use Revolution\Copilot\Types\Rpc\AutopilotObjectiveState;

describe('PendingAutopilotObjective', function () {
    it('reads the typed objective state using the session id', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.autopilotObjective.getState', ['sessionId' => 'session-1'])
            ->andReturn([
                'state' => [
                    'id' => 12,
                    'objective' => 'Finish the task',
                    'status' => 'paused',
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
            ]);

        $pending = (new SessionRpc($client, 'session-1'))->autopilotObjective();
        $result = $pending->getState();

        expect($pending)->toBeInstanceOf(PendingAutopilotObjective::class)
            ->and($result)->toBeInstanceOf(AutopilotObjectiveGetStateResult::class)
            ->and($result->state)->toBeInstanceOf(AutopilotObjectiveState::class)
            ->and($result->state->status)->toBe(AutopilotObjectiveStatus::PAUSED)
            ->and($result->state->turnCount)->toBe(4)
            ->and($result->state->creditLimit)->toBeInstanceOf(AutopilotObjectiveCreditLimit::class)
            ->and($result->state->creditLimit->creditsUsed)->toBe(3.75);
    });

    it('returns a null state when no objective exists', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.autopilotObjective.getState', ['sessionId' => 'session-1'])
            ->andReturn(['state' => null]);

        $result = (new PendingAutopilotObjective($client, 'session-1'))->getState();

        expect($result->state)->toBeNull()
            ->and($result->toArray())->toBe(['state' => null]);
    });
});
