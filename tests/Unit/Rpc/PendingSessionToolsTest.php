<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingTools;
use Revolution\Copilot\Types\Rpc\HandleToolCallResult;
use Revolution\Copilot\Types\Rpc\ToolsHandlePendingToolCallRequest;

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
            new ToolsHandlePendingToolCallRequest(requestId: 'req-1', result: 'tool output'),
        );

        expect($result)->toBeInstanceOf(HandleToolCallResult::class)
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

        expect($result)->toBeInstanceOf(HandleToolCallResult::class)
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
            new ToolsHandlePendingToolCallRequest(requestId: 'req-3', error: 'something went wrong'),
        );

        expect($result)->toBeInstanceOf(HandleToolCallResult::class)
            ->and($result->success)->toBeFalse();
    });
});
