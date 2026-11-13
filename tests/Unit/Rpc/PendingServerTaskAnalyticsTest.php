<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerTaskAnalytics;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsClassifyTrajectoryRequest;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsClassifyTrajectoryResult;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsTrajectoryValidation;

describe('PendingServerTaskAnalytics', function () {
    it('dispatches typed and array parameters and returns typed classifications', function () {
        $params = [
            'events' => [['type' => 'user.message', 'id' => 'event-1']],
            'classifiers' => ['task-outcome'],
            'maxConcurrentRequests' => 3,
            'judge' => ['model' => 'gpt-5'],
            'validateOnly' => false,
        ];
        $response = [
            'classifications' => [
                [
                    'classifier' => 'task-outcome',
                    'classifierVersion' => 1,
                    'scope' => 'task',
                    'unitId' => 'unit-1',
                    'ordinal' => 1,
                    'closeReason' => 'turn-completed',
                    'status' => 'success',
                    'labels' => ['outcome' => 'completed'],
                    'metrics' => [],
                    'textTruncated' => false,
                    'unresolvedBoundaries' => 0,
                ],
            ],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('taskAnalytics.classifyTrajectory', $params)
            ->andReturn($response, $response);

        $analytics = (new ServerRpc($client))->taskAnalytics();
        $typedResult = $analytics->classifyTrajectory(TaskAnalyticsClassifyTrajectoryRequest::fromArray($params));
        $arrayResult = $analytics->classifyTrajectory($params);

        expect($analytics)->toBeInstanceOf(PendingServerTaskAnalytics::class)
            ->and($typedResult)->toBeInstanceOf(TaskAnalyticsClassifyTrajectoryResult::class)
            ->and($typedResult->classifications[0]->classifier)->toBe('task-outcome')
            ->and($arrayResult->classifications[0]->toArray())->toBe($response['classifications'][0]);
    });

    it('returns validation diagnostics for a validate-only request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('taskAnalytics.classifyTrajectory', [
                'events' => [],
                'validateOnly' => true,
            ])
            ->andReturn([
                'classifications' => [],
                'validation' => [
                    'valid' => false,
                    'inputBytes' => 2,
                    'errors' => ['No events were supplied.'],
                ],
            ]);

        $result = (new ServerRpc($client))->taskAnalytics()->classifyTrajectory([
            'events' => [],
            'validateOnly' => true,
        ]);

        expect($result->classifications)->toBeEmpty()
            ->and($result->validation)->toBeInstanceOf(TaskAnalyticsTrajectoryValidation::class)
            ->and($result->validation->valid)->toBeFalse()
            ->and($result->validation->errors)->toBe(['No events were supplied.']);
    });
});
