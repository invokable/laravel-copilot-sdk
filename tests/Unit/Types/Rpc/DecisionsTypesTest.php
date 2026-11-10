<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\DecisionAnswer;
use Revolution\Copilot\Types\Rpc\DecisionContext;
use Revolution\Copilot\Types\Rpc\DecisionModelInfo;
use Revolution\Copilot\Types\Rpc\DecisionOption;
use Revolution\Copilot\Types\Rpc\DecisionOptionProbability;
use Revolution\Copilot\Types\Rpc\DecisionQuestion;
use Revolution\Copilot\Types\Rpc\DecisionsAskRequest;
use Revolution\Copilot\Types\Rpc\DecisionsAskResult;
use Revolution\Copilot\Types\Rpc\DecisionUsage;

describe('DecisionContext', function () {
    it('parses and serializes text and JSON contexts by kind', function () {
        $text = DecisionContext::fromArray([
            'kind' => 'text',
            'text' => 'The service returns a 500 error.',
        ]);
        $json = DecisionContext::fromArray([
            'kind' => 'json',
            'value' => ['status' => 500, 'retryable' => false],
        ]);

        expect($text->text)->toBe('The service returns a 500 error.')
            ->and($text->toArray())->toBe([
                'kind' => 'text',
                'text' => 'The service returns a 500 error.',
            ])
            ->and($json->value)->toBe(['status' => 500, 'retryable' => false])
            ->and($json->toArray())->toBe([
                'kind' => 'json',
                'value' => ['status' => 500, 'retryable' => false],
            ]);
    });
});

describe('DecisionQuestion', function () {
    it('parses boolean and choice questions by kind', function () {
        $questions = array_map(
            fn (array $question) => DecisionQuestion::fromArray($question),
            [
                [
                    'kind' => 'boolean',
                    'name' => 'isUrgent',
                    'instructions' => 'Is this urgent?',
                    'whenTrue' => 'Needs attention today',
                    'whenFalse' => 'Can wait',
                ],
                [
                    'kind' => 'choice',
                    'name' => 'owner',
                    'instructions' => 'Who should own this?',
                    'options' => [
                        ['value' => 'platform', 'description' => 'Platform team'],
                        ['value' => 'product', 'description' => 'Product team'],
                    ],
                ],
            ],
        );

        expect($questions[0]->kind)->toBe('boolean')
            ->and($questions[0]->whenTrue)->toBe('Needs attention today')
            ->and($questions[0]->whenFalse)->toBe('Can wait')
            ->and($questions[0]->toArray())->toBe([
                'kind' => 'boolean',
                'name' => 'isUrgent',
                'instructions' => 'Is this urgent?',
                'whenTrue' => 'Needs attention today',
                'whenFalse' => 'Can wait',
            ])
            ->and($questions[1]->kind)->toBe('choice')
            ->and($questions[1]->options[0])->toBeInstanceOf(DecisionOption::class)
            ->and($questions[1]->options[0]->value)->toBe('platform')
            ->and($questions[1]->toArray())->toBe([
                'kind' => 'choice',
                'name' => 'owner',
                'instructions' => 'Who should own this?',
                'options' => [
                    ['value' => 'platform', 'description' => 'Platform team'],
                    ['value' => 'product', 'description' => 'Product team'],
                ],
            ]);
    });
});

describe('DecisionAnswer', function () {
    it('parses boolean and choice answers by kind', function () {
        $answers = array_map(
            fn (array $answer) => DecisionAnswer::fromArray($answer),
            [
                [
                    'kind' => 'boolean',
                    'name' => 'isUrgent',
                    'probability' => 0.9,
                ],
                [
                    'kind' => 'choice',
                    'name' => 'owner',
                    'value' => 'platform',
                    'tied' => false,
                    'confidence' => 0.8,
                    'vendorConfidence' => 0.85,
                    'probabilities' => [
                        ['value' => 'platform', 'probability' => 0.8],
                        ['value' => 'product', 'probability' => 0.2],
                    ],
                ],
            ],
        );

        expect($answers[0]->kind)->toBe('boolean')
            ->and($answers[0]->probability)->toBe(0.9)
            ->and($answers[0]->toArray())->toBe([
                'kind' => 'boolean',
                'name' => 'isUrgent',
                'probability' => 0.9,
            ])
            ->and($answers[1]->kind)->toBe('choice')
            ->and($answers[1]->value)->toBe('platform')
            ->and($answers[1]->tied)->toBeFalse()
            ->and($answers[1]->probabilities[0])->toBeInstanceOf(DecisionOptionProbability::class)
            ->and($answers[1]->toArray())->toBe([
                'kind' => 'choice',
                'name' => 'owner',
                'value' => 'platform',
                'tied' => false,
                'confidence' => 0.8,
                'vendorConfidence' => 0.85,
                'probabilities' => [
                    ['value' => 'platform', 'probability' => 0.8],
                    ['value' => 'product', 'probability' => 0.2],
                ],
            ]);
    });
});

describe('DecisionsAskRequest and DecisionsAskResult', function () {
    it('maps nested contexts, questions, answers, and usage', function () {
        $request = DecisionsAskRequest::fromArray([
            'selectionId' => 'account-1',
            'model' => 'decision-model',
            'context' => ['kind' => 'json', 'value' => ['issue' => 42]],
            'questions' => [[
                'kind' => 'boolean',
                'name' => 'isBlocked',
                'instructions' => 'Is the issue blocked?',
            ]],
            'timeoutMs' => 5000,
            'purpose' => 'triage',
        ]);
        $result = DecisionsAskResult::fromArray([
            'model' => 'decision-model',
            'upstreamModel' => 'backend-model',
            'answers' => [[
                'kind' => 'boolean',
                'name' => 'isBlocked',
                'probability' => 0.95,
            ]],
            'usage' => [
                'inputTokens' => 120,
                'cachedInputTokens' => 20,
                'outputTokens' => 12,
            ],
            'latencyMs' => 450,
        ]);

        expect($request->context)->toBeInstanceOf(DecisionContext::class)
            ->and($request->questions[0])->toBeInstanceOf(DecisionQuestion::class)
            ->and($request->toArray())->toBe([
                'selectionId' => 'account-1',
                'model' => 'decision-model',
                'context' => ['kind' => 'json', 'value' => ['issue' => 42]],
                'questions' => [[
                    'kind' => 'boolean',
                    'name' => 'isBlocked',
                    'instructions' => 'Is the issue blocked?',
                ]],
                'timeoutMs' => 5000,
                'purpose' => 'triage',
            ]);

        expect($result->answers[0])->toBeInstanceOf(DecisionAnswer::class)
            ->and($result->usage)->toBeInstanceOf(DecisionUsage::class)
            ->and($result->usage->cachedInputTokens)->toBe(20)
            ->and($result->toArray())->toBe([
                'model' => 'decision-model',
                'upstreamModel' => 'backend-model',
                'answers' => [[
                    'kind' => 'boolean',
                    'name' => 'isBlocked',
                    'probability' => 0.95,
                ]],
                'usage' => [
                    'inputTokens' => 120,
                    'cachedInputTokens' => 20,
                    'outputTokens' => 12,
                ],
                'latencyMs' => 450,
            ]);
    });
});

describe('DecisionModelInfo', function () {
    it('preserves decision model metadata and limits', function () {
        $model = DecisionModelInfo::fromArray([
            'id' => 'decision-model',
            'name' => 'Decision Model',
            'isDefault' => true,
            'limits' => [
                'maxQuestions' => 5,
                'minOptions' => 2,
                'maxOptions' => 6,
                'minLevels' => 2,
                'maxLevels' => 6,
                'maxRequestBytes' => 16384,
                'maxJsonDepth' => 8,
            ],
            'supportsRefusals' => false,
        ]);

        expect($model->id)->toBe('decision-model')
            ->and($model->limits['maxQuestions'])->toBe(5)
            ->and($model->supportsRefusals)->toBeFalse()
            ->and($model->toArray())->toBe([
                'id' => 'decision-model',
                'name' => 'Decision Model',
                'isDefault' => true,
                'limits' => [
                    'maxQuestions' => 5,
                    'minOptions' => 2,
                    'maxOptions' => 6,
                    'minLevels' => 2,
                    'maxLevels' => 6,
                    'maxRequestBytes' => 16384,
                    'maxJsonDepth' => 8,
                ],
                'supportsRefusals' => false,
            ]);
    });
});
