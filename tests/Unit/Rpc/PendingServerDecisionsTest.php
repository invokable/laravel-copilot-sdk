<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerDecisions;
use Revolution\Copilot\Types\Rpc\DecisionContext;
use Revolution\Copilot\Types\Rpc\DecisionOption;
use Revolution\Copilot\Types\Rpc\DecisionQuestion;
use Revolution\Copilot\Types\Rpc\DecisionsAskRequest;
use Revolution\Copilot\Types\Rpc\DecisionsAskResult;

describe('PendingServerDecisions', function () {
    it('sends an array request and returns typed boolean answers', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('decisions.ask', [
                'context' => ['kind' => 'text', 'text' => 'The service is failing.'],
                'questions' => [[
                    'kind' => 'boolean',
                    'name' => 'isUrgent',
                    'instructions' => 'Is this urgent?',
                ]],
            ])
            ->andReturn([
                'model' => 'decision-model',
                'answers' => [[
                    'kind' => 'boolean',
                    'name' => 'isUrgent',
                    'probability' => 0.9,
                ]],
                'latencyMs' => 250,
            ]);

        $result = (new PendingServerDecisions($client))->ask([
            'context' => ['kind' => 'text', 'text' => 'The service is failing.'],
            'questions' => [[
                'kind' => 'boolean',
                'name' => 'isUrgent',
                'instructions' => 'Is this urgent?',
            ]],
        ]);

        expect($result)->toBeInstanceOf(DecisionsAskResult::class)
            ->and($result->answers[0]->kind)->toBe('boolean')
            ->and($result->answers[0]->probability)->toBe(0.9);
    });

    it('sends a typed request and returns typed choice answers', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('decisions.ask', [
                'model' => 'decision-model',
                'context' => ['kind' => 'json', 'value' => ['issue' => 42]],
                'questions' => [[
                    'kind' => 'choice',
                    'name' => 'owner',
                    'instructions' => 'Who should own this?',
                    'options' => [['value' => 'platform', 'description' => 'Platform team']],
                ]],
            ])
            ->andReturn([
                'model' => 'decision-model',
                'answers' => [[
                    'kind' => 'choice',
                    'name' => 'owner',
                    'value' => 'platform',
                    'tied' => false,
                    'confidence' => 1.0,
                    'probabilities' => [['value' => 'platform', 'probability' => 1.0]],
                ]],
                'latencyMs' => 325,
            ]);

        $request = new DecisionsAskRequest(
            context: new DecisionContext(kind: 'json', value: ['issue' => 42]),
            questions: [
                new DecisionQuestion(
                    kind: 'choice',
                    name: 'owner',
                    instructions: 'Who should own this?',
                    options: [new DecisionOption(value: 'platform', description: 'Platform team')],
                ),
            ],
            model: 'decision-model',
        );

        $result = (new PendingServerDecisions($client))->ask($request);

        expect($result->answers[0]->kind)->toBe('choice')
            ->and($result->answers[0]->value)->toBe('platform')
            ->and($result->answers[0]->tied)->toBeFalse();
    });
});
