<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\McpOauthPendingRequestResponseKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingServerLlmInference;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseStartRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseStartResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceSetProviderResult;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingRequest;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingResult;
use Revolution\Copilot\Types\Rpc\McpOauthPendingRequestResponse;

describe('PendingServerLlmInference', function () {
    it('calls llmInference.setProvider and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('llmInference.setProvider', [])
            ->andReturn(['success' => true]);

        $pending = new PendingServerLlmInference($client);
        $result = $pending->setProvider();

        expect($result)->toBeInstanceOf(LlmInferenceSetProviderResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls llmInference.httpResponseStart with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'llmInference.httpResponseStart',
                Mockery::on(fn ($p) => $p['requestId'] === 'req-1' && $p['status'] === 200),
            )
            ->andReturn(['accepted' => true]);

        $pending = new PendingServerLlmInference($client);
        $result = $pending->httpResponseStart(new LlmInferenceHTTPResponseStartRequest(
            headers: ['Content-Type' => ['application/json']],
            requestId: 'req-1',
            status: 200,
        ));

        expect($result)->toBeInstanceOf(LlmInferenceHTTPResponseStartResult::class)
            ->and($result->accepted)->toBeTrue();
    });

    it('calls llmInference.httpResponseStart with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'llmInference.httpResponseStart',
                Mockery::on(fn ($p) => $p['status'] === 404),
            )
            ->andReturn(['accepted' => false]);

        $pending = new PendingServerLlmInference($client);
        $result = $pending->httpResponseStart([
            'headers' => [],
            'requestId' => 'req-2',
            'status' => 404,
        ]);

        expect($result->accepted)->toBeFalse();
    });

    it('calls llmInference.httpResponseChunk with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'llmInference.httpResponseChunk',
                Mockery::on(fn ($p) => $p['requestId'] === 'req-1' && $p['end'] === true),
            )
            ->andReturn(['accepted' => true]);

        $pending = new PendingServerLlmInference($client);
        $result = $pending->httpResponseChunk(new LlmInferenceHTTPResponseChunkRequest(
            data: 'final chunk',
            requestId: 'req-1',
            end: true,
        ));

        expect($result)->toBeInstanceOf(LlmInferenceHTTPResponseChunkResult::class)
            ->and($result->accepted)->toBeTrue();
    });

    it('calls llmInference.httpResponseChunk with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'llmInference.httpResponseChunk',
                Mockery::on(fn ($p) => $p['data'] === 'partial'),
            )
            ->andReturn(['accepted' => true]);

        $pending = new PendingServerLlmInference($client);
        $result = $pending->httpResponseChunk([
            'data' => 'partial',
            'requestId' => 'req-3',
        ]);

        expect($result->accepted)->toBeTrue();
    });
});

describe('PendingMcp handlePendingRequest', function () {
    it('calls session.mcp.oauth.handlePendingRequest with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.oauth.handlePendingRequest',
                Mockery::on(fn ($p) => $p['sessionId'] === 'session-abc'
                    && $p['requestId'] === 'oauth-req-1'
                    && $p['result']['kind'] === 'token'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->handlePendingRequest(new McpOauthHandlePendingRequest(
            requestId: 'oauth-req-1',
            result: new McpOauthPendingRequestResponse(
                kind: McpOauthPendingRequestResponseKind::Token,
                accessToken: 'tok_abc',
            ),
        ));

        expect($result)->toBeInstanceOf(McpOauthHandlePendingResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls session.mcp.oauth.handlePendingRequest with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.oauth.handlePendingRequest',
                Mockery::on(fn ($p) => $p['requestId'] === 'oauth-req-2'
                    && $p['result']['kind'] === 'cancelled'),
            )
            ->andReturn(['success' => false]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->handlePendingRequest([
            'requestId' => 'oauth-req-2',
            'result' => ['kind' => 'cancelled'],
        ]);

        expect($result)->toBeInstanceOf(McpOauthHandlePendingResult::class)
            ->and($result->success)->toBeFalse();
    });
});
