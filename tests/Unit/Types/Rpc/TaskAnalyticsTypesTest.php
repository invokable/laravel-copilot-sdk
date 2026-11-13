<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsClassifyTrajectoryRequest;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsClassifyTrajectoryResult;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsJudge;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsTrajectoryClassification;
use Revolution\Copilot\Types\Rpc\TaskAnalyticsTrajectoryValidation;

describe('TaskAnalyticsClassifyTrajectoryRequest', function () {
    it('round trips trajectory events and optional classifier settings', function () {
        $params = [
            'events' => [
                ['type' => 'user.message', 'id' => 'event-1', 'data' => ['content' => 'hello']],
                ['steps' => [['role' => 'assistant', 'content' => 'hi']]],
            ],
            'classifiers' => ['task-outcome'],
            'maxConcurrentRequests' => 2,
            'judge' => ['model' => 'gpt-5', 'reasoningEffort' => 'high'],
            'validateOnly' => true,
            'previousResult' => [
                'classifications' => [],
                'trajectoryFingerprint' => 'fingerprint-1',
            ],
        ];

        $request = TaskAnalyticsClassifyTrajectoryRequest::fromArray($params);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->judge)->toBeInstanceOf(TaskAnalyticsJudge::class)
            ->and($request->previousResult)->toBeInstanceOf(TaskAnalyticsClassifyTrajectoryResult::class)
            ->and($request->toArray())->toBe($params);
    });

    it('omits unset options while retaining an empty event list', function () {
        $request = new TaskAnalyticsClassifyTrajectoryRequest(events: []);

        expect($request->toArray())->toBe(['events' => []]);
    });
});

describe('TaskAnalyticsClassifyTrajectoryResult', function () {
    it('converts classifications and validation diagnostics to typed values', function () {
        $response = [
            'classifications' => [
                [
                    'classifier' => 'task-outcome',
                    'classifierVersion' => 3,
                    'taxonomyVersion' => 2,
                    'scope' => 'task',
                    'unitId' => 'unit-1',
                    'ordinal' => 1,
                    'anchorEventId' => 'event-1',
                    'closeReason' => 'turn-completed',
                    'status' => 'success',
                    'labels' => ['outcome' => 'completed'],
                    'metrics' => ['confidence' => 0.94],
                    'textTruncated' => false,
                    'unresolvedBoundaries' => 0,
                    'firstMemberOrdinal' => 1,
                    'lastMemberOrdinal' => 2,
                    'model' => 'gpt-5',
                    'interactionId' => 'row-interaction-1',
                    'modelSelectionReason' => 'production-policy',
                    'reasoningEffort' => 'high',
                    'inputTokens' => 120,
                    'outputTokens' => 32,
                    'cachedInputTokens' => 12,
                    'errorKind' => 'rate-limited',
                    'httpStatus' => 429,
                    'retryable' => true,
                    'retryAfterSeconds' => 5.5,
                ],
            ],
            'trajectoryFingerprint' => 'fingerprint-1',
            'interactionId' => 'interaction-1',
            'validation' => ['valid' => true, 'inputBytes' => 512, 'errors' => []],
        ];

        $result = TaskAnalyticsClassifyTrajectoryResult::fromArray($response);

        expect($result->classifications[0])->toBeInstanceOf(TaskAnalyticsTrajectoryClassification::class)
            ->and($result->classifications[0]->labels)->toBe(['outcome' => 'completed'])
            ->and($result->classifications[0]->metrics)->toBe(['confidence' => 0.94])
            ->and($result->classifications[0]->inputTokens)->toBe(120)
            ->and($result->trajectoryFingerprint)->toBe('fingerprint-1')
            ->and($result->validation)->toBeInstanceOf(TaskAnalyticsTrajectoryValidation::class)
            ->and($result->validation->valid)->toBeTrue()
            ->and($result->toArray())->toBe($response);
    });

    it('omits unset classification metadata', function () {
        $classification = TaskAnalyticsTrajectoryClassification::fromArray([
            'classifier' => 'task-outcome',
            'classifierVersion' => 1,
            'scope' => 'task',
            'unitId' => 'unit-1',
            'ordinal' => 1,
            'closeReason' => 'turn-completed',
            'status' => 'success',
            'labels' => [],
            'metrics' => [],
            'textTruncated' => false,
            'unresolvedBoundaries' => 0,
        ]);

        expect($classification->toArray())->toBe([
            'classifier' => 'task-outcome',
            'classifierVersion' => 1,
            'scope' => 'task',
            'unitId' => 'unit-1',
            'ordinal' => 1,
            'closeReason' => 'turn-completed',
            'status' => 'success',
            'labels' => [],
            'metrics' => [],
            'textTruncated' => false,
            'unresolvedBoundaries' => 0,
        ]);
    });

    it('preserves local validation results without classifications', function () {
        $result = TaskAnalyticsClassifyTrajectoryResult::fromArray([
            'classifications' => [],
            'validation' => [
                'valid' => false,
                'inputBytes' => 0,
                'errors' => ['No events were supplied.'],
            ],
        ]);

        expect($result->classifications)->toBeEmpty()
            ->and($result->validation?->valid)->toBeFalse()
            ->and($result->validation?->errors)->toBe(['No events were supplied.']);
    });
});
