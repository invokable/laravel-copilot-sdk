<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AzureMlCatalogRetryMode;
use Revolution\Copilot\Enums\AzureMlRegistry;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerAzureMl;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\AzureMlAcquireFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlAcquireFamilyResult;
use Revolution\Copilot\Types\Rpc\AzureMlCancelRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCatalogRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCatalogResult;
use Revolution\Copilot\Types\Rpc\AzureMlDiscardRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadResult;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadStatus;
use Revolution\Copilot\Types\Rpc\AzureMlLatestRequest;
use Revolution\Copilot\Types\Rpc\AzureMlLatestResult;
use Revolution\Copilot\Types\Rpc\AzureMlReleaseFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlResolveRequest;
use Revolution\Copilot\Types\Rpc\AzureMlResolveResult;
use Revolution\Copilot\Types\Rpc\AzureMlStatusRequest;

describe('PendingServerAzureMl', function () {
    it('dispatches every Azure ML operation with typed and array parameters', function () {
        $cases = [
            [
                'catalog',
                'azureMl.catalog',
                ['includeDev' => false, 'retryMode' => 'explicit'],
                new AzureMlCatalogRequest(false, AzureMlCatalogRetryMode::Explicit),
                ['models' => [], 'warnings' => []],
                AzureMlCatalogResult::class,
            ],
            [
                'latest',
                'azureMl.latest',
                ['registry' => 'azureml', 'name' => 'phi'],
                new AzureMlLatestRequest(AzureMlRegistry::AzureMl, 'phi'),
                [
                    'registry' => 'azureml',
                    'name' => 'phi',
                    'version' => '1',
                    'profile' => [
                        'displayName' => 'Phi',
                        'maxPromptTokens' => 100,
                        'maxContextWindowTokens' => 200,
                        'maxOutputTokens' => 50,
                        'supportsToolCalls' => false,
                        'serveOptions' => [],
                    ],
                    'metadata' => [],
                ],
                AzureMlLatestResult::class,
            ],
            [
                'resolve',
                'azureMl.resolve',
                ['registry' => 'azureml', 'name' => 'phi', 'version' => '1'],
                new AzureMlResolveRequest(AzureMlRegistry::AzureMl, 'phi', '1'),
                [
                    'totalBytes' => 10,
                    'primaryPath' => 'phi/model.gguf',
                    'extraFiles' => [],
                    'serveOptions' => [],
                    'maxConcurrent' => 1,
                ],
                AzureMlResolveResult::class,
            ],
            [
                'download',
                'azureMl.download',
                [
                    'requestId' => 'request-1',
                    'registry' => 'azureml',
                    'name' => 'phi',
                    'version' => '1',
                    'destination' => 'models/phi/1',
                    'leaseId' => 'lease-1',
                ],
                new AzureMlDownloadRequest('request-1', AzureMlRegistry::AzureMl, 'phi', '1', 'models/phi/1', 'lease-1'),
                [
                    'primaryPath' => 'models/phi/1/model.gguf',
                    'manifestPath' => 'models/phi/1/manifest.json',
                    'totalBytes' => 10,
                    'reused' => false,
                    'serveOptions' => [],
                    'maxConcurrent' => 1,
                    'extraFiles' => [],
                ],
                AzureMlDownloadResult::class,
            ],
            [
                'acquireFamily',
                'azureMl.acquireFamily',
                ['registry' => 'azureml', 'name' => 'phi'],
                new AzureMlAcquireFamilyRequest(AzureMlRegistry::AzureMl, 'phi'),
                ['leaseId' => 'lease-1'],
                AzureMlAcquireFamilyResult::class,
            ],
            [
                'releaseFamily',
                'azureMl.releaseFamily',
                ['leaseId' => 'lease-1', 'registry' => 'azureml', 'name' => 'phi'],
                new AzureMlReleaseFamilyRequest('lease-1', AzureMlRegistry::AzureMl, 'phi'),
                null,
                null,
            ],
            [
                'status',
                'azureMl.status',
                ['requestId' => 'request-1'],
                new AzureMlStatusRequest('request-1'),
                ['phase' => 'downloading', 'completedBytes' => 4, 'totalBytes' => 10],
                AzureMlDownloadStatus::class,
            ],
            [
                'cancel',
                'azureMl.cancel',
                ['requestId' => 'request-1'],
                new AzureMlCancelRequest('request-1'),
                true,
                'bool',
            ],
            [
                'discard',
                'azureMl.discard',
                ['destination' => 'models/phi/1', 'leaseId' => 'lease-1'],
                new AzureMlDiscardRequest('models/phi/1', 'lease-1'),
                null,
                null,
            ],
        ];
        $client = Mockery::mock(JsonRpcClient::class);

        foreach ($cases as [$method, $rpcMethod, $params, $typedParams, $response]) {
            $expectation = $client->shouldReceive('request')
                ->twice();

            if ($method === 'download') {
                $expectation->with($rpcMethod, $params, null);
            } else {
                $expectation->with($rpcMethod, $params);
            }

            $expectation->andReturn($response, $response);
        }

        $azureMl = (new ServerRpc($client))->azureMl();

        expect($azureMl)->toBeInstanceOf(PendingServerAzureMl::class);

        foreach ($cases as [$method, , $params, $typedParams, $response, $resultType]) {
            $typedResult = $azureMl->{$method}($typedParams);
            $arrayResult = $azureMl->{$method}($params);

            if ($resultType === null) {
                expect($typedResult)->toBeNull()
                    ->and($arrayResult)->toBeNull();

                continue;
            }

            if ($resultType === 'bool') {
                expect($typedResult)->toBe($response)
                    ->and($arrayResult)->toBe($response);

                continue;
            }

            expect($typedResult)->toBeInstanceOf($resultType)
                ->and($typedResult->toArray())->toBe($response)
                ->and($arrayResult)->toBeInstanceOf($resultType)
                ->and($arrayResult->toArray())->toBe($response);
        }
    });

    it('returns null when the connection has no active download status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('azureMl.status', ['requestId' => 'request-1'])
            ->andReturn(null);

        $result = (new PendingServerAzureMl($client))->status(new AzureMlStatusRequest('request-1'));

        expect($result)->toBeNull();
    });

    it('allows an explicit timeout for a long-running download', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('azureMl.download', [
                'requestId' => 'request-1',
                'registry' => 'azureml',
                'name' => 'phi',
                'version' => '1',
                'destination' => 'models/phi/1',
            ], 1200.0)
            ->andReturn([
                'primaryPath' => 'models/phi/1/model.gguf',
                'manifestPath' => 'models/phi/1/manifest.json',
                'totalBytes' => 10,
                'reused' => false,
                'serveOptions' => [],
                'maxConcurrent' => 1,
                'extraFiles' => [],
            ]);

        $result = (new PendingServerAzureMl($client))->download(
            new AzureMlDownloadRequest('request-1', AzureMlRegistry::AzureMl, 'phi', '1', 'models/phi/1'),
            timeout: 1200.0,
        );

        expect($result->primaryPath)->toBe('models/phi/1/model.gguf');
    });
});
