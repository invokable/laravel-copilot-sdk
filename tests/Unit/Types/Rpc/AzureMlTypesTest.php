<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AzureMlCatalogRetryMode;
use Revolution\Copilot\Enums\AzureMlRegistry;
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

describe('Azure ML RPC types', function () {
    it('round trips typed requests and preserves unknown enum values', function () {
        $requests = [
            [
                new AzureMlCatalogRequest(false, AzureMlCatalogRetryMode::Explicit),
                ['includeDev' => false, 'retryMode' => 'explicit'],
                AzureMlCatalogRequest::class,
            ],
            [
                new AzureMlLatestRequest(AzureMlRegistry::AzureMl, 'phi'),
                ['registry' => 'azureml', 'name' => 'phi'],
                AzureMlLatestRequest::class,
            ],
            [
                new AzureMlResolveRequest(AzureMlRegistry::AzureMl, 'phi', '1'),
                ['registry' => 'azureml', 'name' => 'phi', 'version' => '1'],
                AzureMlResolveRequest::class,
            ],
            [
                new AzureMlDownloadRequest('request-1', AzureMlRegistry::AzureMl, 'phi', '1', 'models/phi/1', 'lease-1'),
                [
                    'requestId' => 'request-1',
                    'registry' => 'azureml',
                    'name' => 'phi',
                    'version' => '1',
                    'destination' => 'models/phi/1',
                    'leaseId' => 'lease-1',
                ],
                AzureMlDownloadRequest::class,
            ],
            [
                new AzureMlAcquireFamilyRequest(AzureMlRegistry::AzureMl, 'phi'),
                ['registry' => 'azureml', 'name' => 'phi'],
                AzureMlAcquireFamilyRequest::class,
            ],
            [
                new AzureMlReleaseFamilyRequest('lease-1', AzureMlRegistry::AzureMl, 'phi'),
                ['leaseId' => 'lease-1', 'registry' => 'azureml', 'name' => 'phi'],
                AzureMlReleaseFamilyRequest::class,
            ],
            [
                new AzureMlStatusRequest('request-1'),
                ['requestId' => 'request-1'],
                AzureMlStatusRequest::class,
            ],
            [
                new AzureMlCancelRequest('request-1'),
                ['requestId' => 'request-1'],
                AzureMlCancelRequest::class,
            ],
            [
                new AzureMlDiscardRequest('models/phi/1', 'lease-1'),
                ['destination' => 'models/phi/1', 'leaseId' => 'lease-1'],
                AzureMlDiscardRequest::class,
            ],
        ];

        foreach ($requests as [$request, $expected, $type]) {
            expect($request->toArray())->toBe($expected)
                ->and($type::fromArray($expected)->toArray())->toBe($expected);
        }

        $unknownRegistry = AzureMlLatestRequest::fromArray([
            'registry' => 'future-registry',
            'name' => 'phi',
        ]);
        $unknownRetryMode = AzureMlCatalogRequest::fromArray([
            'includeDev' => false,
            'retryMode' => 'future-mode',
        ]);

        expect($unknownRegistry->registry)->toBe('future-registry')
            ->and($unknownRetryMode->retryMode)->toBe('future-mode')
            ->and(AzureMlRegistry::AzureMlDev->value)->toBe('azureml-dev');
    });

    it('round trips the catalog and nested Azure ML model metadata', function () {
        $data = [
            'models' => [[
                'registry' => 'azureml',
                'name' => 'phi',
                'version' => '1',
                'profile' => [
                    'displayName' => 'Phi',
                    'maxPromptTokens' => 1024,
                    'maxContextWindowTokens' => 4096,
                    'maxOutputTokens' => 512,
                    'supportsToolCalls' => false,
                    'serveOptions' => ['contextLength' => 4096],
                    'downloadBytes' => 100,
                    'license' => ['name' => 'MIT'],
                ],
                'metadata' => ['provider' => 'azureml'],
                'signedDownloadUrl' => 'must-not-be-exposed',
            ]],
            'warnings' => [[
                'registry' => 'azureml-dev',
                'modelName' => 'future-model',
                'version' => '2',
                'kind' => 'unavailable',
                'downloadUrl' => 'must-not-be-exposed',
            ]],
        ];

        $result = AzureMlCatalogResult::fromArray($data);

        expect($result->models[0]->registry)->toBe(AzureMlRegistry::AzureMl)
            ->and($result->models[0]->profile->license->name)->toBe('MIT')
            ->and($result->models[0]->profile->supportsToolCalls)->toBeFalse()
            ->and($result->models[0]->metadata)->toBe(['provider' => 'azureml'])
            ->and($result->warnings[0]->registry)->toBe(AzureMlRegistry::AzureMlDev)
            ->and($result->toArray())->toBe([
                'models' => [[
                    'registry' => 'azureml',
                    'name' => 'phi',
                    'version' => '1',
                    'profile' => [
                        'displayName' => 'Phi',
                        'maxPromptTokens' => 1024,
                        'maxContextWindowTokens' => 4096,
                        'maxOutputTokens' => 512,
                        'supportsToolCalls' => false,
                        'serveOptions' => ['contextLength' => 4096],
                        'downloadBytes' => 100,
                        'license' => ['name' => 'MIT'],
                    ],
                    'metadata' => ['provider' => 'azureml'],
                ]],
                'warnings' => [[
                    'registry' => 'azureml-dev',
                    'modelName' => 'future-model',
                    'version' => '2',
                    'kind' => 'unavailable',
                ]],
            ]);
    });

    it('round trips results without exposing unknown signed URL fields', function () {
        $latest = AzureMlLatestResult::fromArray([
            'registry' => 'azureml',
            'name' => 'phi',
            'version' => '1',
            'profile' => [
                'displayName' => 'Phi',
                'maxPromptTokens' => 1024,
                'maxContextWindowTokens' => 4096,
                'maxOutputTokens' => 512,
                'supportsToolCalls' => false,
                'serveOptions' => null,
            ],
            'metadata' => null,
            'signedDownloadUrl' => 'must-not-be-exposed',
        ]);
        $resolved = AzureMlResolveResult::fromArray([
            'totalBytes' => 100,
            'primaryPath' => 'models/phi/1/model.gguf',
            'extraFiles' => ['tokenizer.json'],
            'serveOptions' => ['contextLength' => 4096],
            'maxConcurrent' => 2,
            'downloadUrl' => 'must-not-be-exposed',
        ]);
        $download = AzureMlDownloadResult::fromArray([
            'primaryPath' => 'models/phi/1/model.gguf',
            'manifestPath' => 'models/phi/1/manifest.json',
            'totalBytes' => 100,
            'reused' => false,
            'serveOptions' => ['contextLength' => 4096],
            'maxConcurrent' => 2,
            'extraFiles' => ['tokenizer.json'],
            'totalFailures' => 0,
            'receivedBytes' => 100,
            'resumed' => false,
            'signedDownloadUrl' => 'must-not-be-exposed',
        ]);
        $status = AzureMlDownloadStatus::fromArray([
            'phase' => 'downloading',
            'completedBytes' => 50,
            'totalBytes' => 100,
            'retryAttempt' => 2,
            'maxAttempts' => 4,
            'retryReason' => 'transient',
            'retryDelayMs' => 500,
            'downloadUrl' => 'must-not-be-exposed',
        ]);
        $lease = AzureMlAcquireFamilyResult::fromArray(['leaseId' => 'lease-1']);

        expect($latest->toArray())->toBe([
            'registry' => 'azureml',
            'name' => 'phi',
            'version' => '1',
            'profile' => [
                'displayName' => 'Phi',
                'maxPromptTokens' => 1024,
                'maxContextWindowTokens' => 4096,
                'maxOutputTokens' => 512,
                'supportsToolCalls' => false,
                'serveOptions' => null,
            ],
            'metadata' => null,
        ])
            ->and($resolved->toArray())->toBe([
                'totalBytes' => 100,
                'primaryPath' => 'models/phi/1/model.gguf',
                'extraFiles' => ['tokenizer.json'],
                'serveOptions' => ['contextLength' => 4096],
                'maxConcurrent' => 2,
            ])
            ->and($download->toArray())->toBe([
                'primaryPath' => 'models/phi/1/model.gguf',
                'manifestPath' => 'models/phi/1/manifest.json',
                'totalBytes' => 100,
                'reused' => false,
                'serveOptions' => ['contextLength' => 4096],
                'maxConcurrent' => 2,
                'extraFiles' => ['tokenizer.json'],
                'totalFailures' => 0,
                'receivedBytes' => 100,
                'resumed' => false,
            ])
            ->and($status->toArray())->toBe([
                'phase' => 'downloading',
                'completedBytes' => 50,
                'totalBytes' => 100,
                'retryAttempt' => 2,
                'maxAttempts' => 4,
                'retryReason' => 'transient',
                'retryDelayMs' => 500,
            ])
            ->and($lease->toArray())->toBe(['leaseId' => 'lease-1']);
    });
});
