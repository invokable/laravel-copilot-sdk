<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMetadata;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoRequest;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoResult;
use Revolution\Copilot\Types\Rpc\MetadataIsProcessingResult;
use Revolution\Copilot\Types\Rpc\MetadataRecomputeContextTokensRequest;
use Revolution\Copilot\Types\Rpc\MetadataRecomputeContextTokensResult;
use Revolution\Copilot\Types\Rpc\MetadataRecordContextChangeRequest;
use Revolution\Copilot\Types\Rpc\MetadataRecordContextChangeResult;
use Revolution\Copilot\Types\Rpc\MetadataSetWorkingDirectoryRequest;
use Revolution\Copilot\Types\Rpc\MetadataSetWorkingDirectoryResult;
use Revolution\Copilot\Types\Rpc\SessionMetadataSnapshot;
use Revolution\Copilot\Types\Rpc\SessionWorkingDirectoryContext;

describe('PendingMetadata', function () {
    it('calls session.metadata.snapshot', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.metadata.snapshot', ['sessionId' => 'test-session'])
            ->andReturn([
                'sessionId' => 'test-session',
                'startTime' => '2026-01-24T10:00:00Z',
                'modifiedTime' => '2026-01-24T10:00:00Z',
                'isRemote' => false,
                'alreadyInUse' => false,
                'workspacePath' => null,
                'workingDirectory' => '/workspace',
                'currentMode' => 'interactive',
            ]);

        $pending = new PendingMetadata($client, 'test-session');
        $result = $pending->snapshot();

        expect($result)->toBeInstanceOf(SessionMetadataSnapshot::class)
            ->and($result->sessionId)->toBe('test-session')
            ->and($result->workingDirectory)->toBe('/workspace');
    });

    it('calls session.metadata.isProcessing', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.metadata.isProcessing', ['sessionId' => 'test-session'])
            ->andReturn(['processing' => true]);

        $pending = new PendingMetadata($client, 'test-session');
        $result = $pending->isProcessing();

        expect($result)->toBeInstanceOf(MetadataIsProcessingResult::class)
            ->and($result->processing)->toBeTrue();
    });

    it('calls session.metadata.contextInfo', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.metadata.contextInfo',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['promptTokenLimit'] === 10000
                    && $params['outputTokenLimit'] === 4096),
            )
            ->andReturn([
                'contextInfo' => [
                    'bufferTokens' => 100,
                    'compactionThreshold' => 5000,
                    'conversationTokens' => 3000,
                    'limit' => 14096,
                    'modelName' => 'gpt-5',
                    'promptTokenLimit' => 10000,
                    'systemTokens' => 500,
                    'toolDefinitionsTokens' => 200,
                    'totalTokens' => 3700,
                ],
            ]);

        $pending = new PendingMetadata($client, 'test-session');
        $result = $pending->contextInfo(new MetadataContextInfoRequest(promptTokenLimit: 10000, outputTokenLimit: 4096));

        expect($result)->toBeInstanceOf(MetadataContextInfoResult::class)
            ->and($result->contextInfo)->not->toBeNull()
            ->and($result->contextInfo->modelName)->toBe('gpt-5');
    });

    it('calls session.metadata.recordContextChange', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.metadata.recordContextChange',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['context']['cwd'] === '/workspace'),
            )
            ->andReturn([]);

        $pending = new PendingMetadata($client, 'test-session');
        $result = $pending->recordContextChange(new MetadataRecordContextChangeRequest(
            context: new SessionWorkingDirectoryContext(cwd: '/workspace'),
        ));

        expect($result)->toBeInstanceOf(MetadataRecordContextChangeResult::class);
    });

    it('calls session.metadata.setWorkingDirectory', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.metadata.setWorkingDirectory',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['workingDirectory'] === '/new/path'),
            )
            ->andReturn(['workingDirectory' => '/new/path']);

        $pending = new PendingMetadata($client, 'test-session');
        $result = $pending->setWorkingDirectory(new MetadataSetWorkingDirectoryRequest(
            workingDirectory: '/new/path',
        ));

        expect($result)->toBeInstanceOf(MetadataSetWorkingDirectoryResult::class)
            ->and($result->workingDirectory)->toBe('/new/path');
    });

    it('calls session.metadata.recomputeContextTokens', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.metadata.recomputeContextTokens',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session'
                    && $params['modelId'] === 'gpt-5'),
            )
            ->andReturn([
                'totalTokens' => 3700,
                'messagesTokenCount' => 3000,
                'systemTokenCount' => 700,
            ]);

        $pending = new PendingMetadata($client, 'test-session');
        $result = $pending->recomputeContextTokens(new MetadataRecomputeContextTokensRequest(
            modelId: 'gpt-5',
        ));

        expect($result)->toBeInstanceOf(MetadataRecomputeContextTokensResult::class)
            ->and($result->totalTokens)->toBe(3700)
            ->and($result->messagesTokenCount)->toBe(3000)
            ->and($result->systemTokenCount)->toBe(700);
    });
});
