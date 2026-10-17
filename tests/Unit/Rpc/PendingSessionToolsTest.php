<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\TaskCompletionOutcome;
use Revolution\Copilot\Enums\ToolResultType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingTools;
use Revolution\Copilot\Types\Rpc\HandlePendingToolCallRequest;
use Revolution\Copilot\Types\Rpc\HandlePendingToolCallResult;
use Revolution\Copilot\Types\Rpc\ToolResultExpanded;
use Revolution\Copilot\Types\Rpc\ToolResultNewMessage;
use Revolution\Copilot\Types\Rpc\ToolsExecuteRequest;
use Revolution\Copilot\Types\Rpc\ToolsSetRequest;

describe('PendingSessionTools', function () {
    it('calls session.tools.handlePendingToolCall with correct params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tools.handlePendingToolCall',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['requestId'] === 'req-1'
                    && $params['result'] === 'tool output'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingTools($client, 'test-session-id');
        $result = $pending->handlePendingToolCall(
            new HandlePendingToolCallRequest(requestId: 'req-1', result: 'tool output'),
        );

        expect($result)->toBeInstanceOf(HandlePendingToolCallResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('overrides sessionId when provided as array param', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tools.handlePendingToolCall',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingTools($client, 'test-session-id');
        $result = $pending->handlePendingToolCall([
            'requestId' => 'req-2',
            'result' => 'output',
            'sessionId' => 'some-other-session',
        ]);

        expect($result)->toBeInstanceOf(HandlePendingToolCallResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('passes error param correctly', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tools.handlePendingToolCall',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['requestId'] === 'req-3'
                    && $params['error'] === 'something went wrong'
                    && ! isset($params['result'])),
            )
            ->andReturn(['success' => false]);

        $pending = new PendingTools($client, 'test-session-id');
        $result = $pending->handlePendingToolCall(
            new HandlePendingToolCallRequest(requestId: 'req-3', error: 'something went wrong'),
        );

        expect($result)->toBeInstanceOf(HandlePendingToolCallResult::class)
            ->and($result->success)->toBeFalse();
    });

    it('replaces external tools with a session-scoped RPC', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tools.set', [
                'tools' => [['name' => 'search', 'description' => 'Search', 'parameters' => []]],
                'sessionId' => 'test-session-id',
            ])
            ->andReturn([]);

        expect((new PendingTools($client, 'test-session-id'))->set(new ToolsSetRequest([
            ['name' => 'search', 'description' => 'Search', 'parameters' => []],
        ])))->toBe([]);
    });

    it('executes a tool with the session scope and serializes empty arguments as an object', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tools.execute',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['name'] === 'search'
                    && json_encode($params['arguments']) === '{}'
                    && $params['toolCallId'] === 'call-1'),
            )
            ->andReturn('tool output');

        $result = (new PendingTools($client, 'test-session-id'))->execute(
            new ToolsExecuteRequest(name: 'search', arguments: [], toolCallId: 'call-1'),
        );

        expect($result)->toBe('tool output');
    });

    it('omits toolCallId and overrides a sessionId supplied in array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.tools.execute',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['name'] === 'search'
                    && $params['arguments'] === ['query']
                    && ! array_key_exists('toolCallId', $params)),
            )
            ->andReturn('array input result');

        $result = (new PendingTools($client, 'test-session-id'))->execute([
            'sessionId' => 'different-session',
            'name' => 'search',
            'arguments' => ['query'],
        ]);

        expect($result)->toBe('array input result');
    });

    it('preserves scalar null and non-empty list arguments as JSON values', function () {
        $arguments = ['plain text', null, ['first', 'second']];
        $serializedArguments = [];

        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->times(3)
            ->with(
                'session.tools.execute',
                Mockery::on(function ($params) use (&$serializedArguments) {
                    $serializedArguments[] = json_encode($params['arguments']);

                    return $params['sessionId'] === 'test-session-id';
                }),
            )
            ->andReturn('done');

        $pending = new PendingTools($client, 'test-session-id');

        foreach ($arguments as $argument) {
            $pending->execute(['name' => 'echo', 'arguments' => $argument]);
        }

        expect($serializedArguments)->toBe(['"plain text"', 'null', '["first","second"]']);
    });

    it('decodes expanded results with nested messages and metadata while preserving unknown result types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.tools.execute', Mockery::type('array'))
            ->andReturn([
                'textResultForLlm' => 'completed',
                'resultType' => 'future-result',
                'newMessages' => [
                    ['content' => 'Injected context', 'source' => 'tool'],
                ],
                'mcpMeta' => ['labels' => ['trusted']],
                'structuredContent' => ['count' => 2, 'valid' => true],
                'taskCompletionDecision' => [
                    'outcome' => 'continue',
                    'reason' => 'More work remains',
                    'reviewerDerived' => false,
                    'reviewerResultMeta' => ['reviewer' => 'completion'],
                    'objectiveId' => 41,
                    'completionRejectionBudgetExhausted' => false,
                    'completionEligibilityToken' => 72,
                ],
            ]);

        $result = (new PendingTools($client, 'test-session-id'))->execute([
            'name' => 'task_complete',
            'arguments' => ['status' => 'done'],
        ]);

        expect($result)->toBeInstanceOf(ToolResultExpanded::class);
        expect($result->resultType)->toBe('future-result');
        expect($result->newMessages[0])->toBeInstanceOf(ToolResultNewMessage::class);
        expect($result->newMessages[0]->content)->toBe('Injected context');
        expect($result->newMessages[0]->source)->toBe('tool');
        expect($result->mcpMeta)->toBe(['labels' => ['trusted']]);
        expect($result->structuredContent)->toBe(['count' => 2, 'valid' => true]);
        expect($result->taskCompletionDecision->outcome)->toBe(TaskCompletionOutcome::Continue);
        expect($result->taskCompletionDecision->reviewerDerived)->toBeFalse();
        expect($result->toArray()['resultType'])->toBe('future-result');
        expect($result->toArray()['taskCompletionDecision']['outcome'])->toBe('continue');

        expect(ToolResultExpanded::fromArray([
            'textResultForLlm' => 'done',
            'resultType' => 'success',
        ])->resultType)->toBe(ToolResultType::Success);
    });
});
