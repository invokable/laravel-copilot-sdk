<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\LocalModelOperation;
use Revolution\Copilot\Types\Rpc\LocalModelDescriptor;
use Revolution\Copilot\Types\Rpc\LocalModelDiskRequirement;
use Revolution\Copilot\Types\Rpc\LocalModelGpuRequirement;
use Revolution\Copilot\Types\Rpc\LocalModelMachineRow;
use Revolution\Copilot\Types\Rpc\LocalModelMachineView;
use Revolution\Copilot\Types\Rpc\LocalModelsConfigureRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsEvent;
use Revolution\Copilot\Types\Rpc\LocalModelsModelIdRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsModelRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsPrepareResult;
use Revolution\Copilot\Types\Rpc\LocalModelsRefreshRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsSetupRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsSnapshot;
use Revolution\Copilot\Types\Rpc\LocalModelStatus;
use Revolution\Copilot\Types\Rpc\LocalModelsView;
use Revolution\Copilot\Types\Rpc\LocalModelsWatchRequest;

describe('LocalModels RPC types', function () {
    it('round trips model descriptors and nullable metadata', function () {
        $data = [
            'id' => 'model-1',
            'displayName' => 'Model One',
            'azureMl' => ['registry' => 'azureml', 'name' => 'model-one', 'version' => '1'],
            'source' => ['id' => 'azureml', 'label' => 'Azure ML', 'ordering' => 1],
            'fileName' => 'model.gguf',
            'provider' => 'Publisher',
            'license' => 'MIT',
            'sourceLabel' => 'Azure ML',
            'approximateBytes' => 1_000,
            'runtime' => 'llama.cpp',
            'runtimeProvisioned' => false,
            'providerName' => 'local',
            'selectionId' => 'local/model-one',
            'lifecycle' => 'preview',
            'serveOptions' => ['contextLength' => 4096],
            'limits' => ['maxPromptTokens' => 100, 'maxContextWindowTokens' => 200, 'maxOutputTokens' => 50],
            'supportsToolCalls' => true,
            'autoLocal' => ['capiId' => 'model-one', 'maxConcurrent' => 2],
            'metadata' => ['publisher' => 'Publisher'],
        ];
        $descriptor = LocalModelDescriptor::fromArray($data);
        $prepared = LocalModelsPrepareResult::fromArray($data);

        expect($descriptor->azureMl->registry)->toBe('azureml')
            ->and($descriptor->source->ordering)->toBe(1)
            ->and($descriptor->limits->maxContextWindowTokens)->toBe(200)
            ->and($descriptor->autoLocal?->maxConcurrent)->toBe(2)
            ->and($descriptor->toArray())->toBe($data)
            ->and($prepared)->toBeInstanceOf(LocalModelsPrepareResult::class)
            ->and($prepared->toArray())->toBe($data);

        $withoutOptional = LocalModelDescriptor::fromArray([
            ...$data,
            'peakDiskBytes' => null,
            'approximateVramBytes' => null,
            'selectionId' => null,
            'lifecycle' => null,
            'autoLocal' => null,
        ]);
        $expectedWithoutOptional = $data;
        unset($expectedWithoutOptional['selectionId'], $expectedWithoutOptional['lifecycle'], $expectedWithoutOptional['autoLocal']);

        expect($withoutOptional->toArray())->toBe($expectedWithoutOptional);
    });

    it('round trips discriminated disk, graphics, and installation states', function () {
        $disk = [
            ['kind' => 'unknown'],
            ['kind' => 'ok', 'freeBytes' => 100],
            ['kind' => 'insufficient', 'freeBytes' => 10, 'requiredBytes' => 20, 'directory' => '/models'],
        ];
        $gpu = [
            ['kind' => 'unknown'],
            ['kind' => 'unspecified'],
            ['kind' => 'ok', 'availableBytes' => 100, 'requiredBytes' => 80, 'description' => 'GPU'],
            ['kind' => 'insufficient', 'availableBytes' => 20, 'requiredBytes' => 80, 'description' => 'GPU'],
        ];
        $statuses = [
            ['kind' => 'not-downloaded'],
            ['kind' => 'downloaded', 'sizeBytes' => 100, 'updateAvailable' => false],
            ['kind' => 'downloading', 'receivedBytes' => 40, 'totalBytes' => 100, 'stage' => 'model'],
        ];

        foreach ($disk as $data) {
            expect(LocalModelDiskRequirement::fromArray($data)->toArray())->toBe($data);
        }

        foreach ($gpu as $data) {
            expect(LocalModelGpuRequirement::fromArray($data)->toArray())->toBe($data);
        }

        foreach ($statuses as $data) {
            expect(LocalModelStatus::fromArray($data)->toArray())->toBe($data);
        }
    });

    it('parses local model state and events into typed values', function () {
        $descriptor = [
            'id' => 'model-1',
            'displayName' => 'Model One',
            'azureMl' => ['registry' => 'azureml', 'name' => 'model-one', 'version' => '1'],
            'source' => ['id' => 'azureml', 'label' => 'Azure ML', 'ordering' => 1],
            'fileName' => 'model.gguf',
            'provider' => 'Publisher',
            'license' => 'MIT',
            'sourceLabel' => 'Azure ML',
            'runtime' => 'llama.cpp',
            'providerName' => 'local',
            'serveOptions' => [],
            'limits' => ['maxPromptTokens' => 100, 'maxContextWindowTokens' => 200, 'maxOutputTokens' => 50],
            'supportsToolCalls' => false,
            'metadata' => [],
        ];
        $data = [
            'revision' => 5,
            'state' => [
                'enabled' => true,
                'machine' => [
                    'llamaServerPath' => '/bin/llama-server',
                    'modelsDirectory' => '/models',
                    'rows' => [[
                        'descriptor' => $descriptor,
                        'status' => [
                            'kind' => 'downloaded',
                            'sizeBytes' => 1_000,
                            'updateAvailable' => false,
                        ],
                        'stagedBytes' => 0,
                        'leftoverBytes' => 12,
                        'disk' => ['kind' => 'ok', 'freeBytes' => 10_000],
                        'gpu' => [
                            'kind' => 'ok',
                            'availableBytes' => 8_000,
                            'requiredBytes' => 4_000,
                            'description' => 'GPU',
                        ],
                        'diskMet' => true,
                    ]],
                ],
                'downloads' => [
                    'model-1' => ['receivedBytes' => 10, 'totalBytes' => 100, 'stage' => 'model'],
                ],
                'operations' => [
                    'model-1' => 'downloading',
                    'future-model' => 'future-operation',
                ],
                'notices' => [
                    'model-1' => ['modelId' => 'model-1', 'tone' => 'info', 'text' => 'Ready'],
                ],
                'catalogLoading' => false,
                'catalogError' => 'stale catalog',
                'hideDevModels' => false,
                'hideWinMlModels' => true,
                'prerequisiteNotice' => 'Install the runtime',
            ],
            'events' => [
                [
                    'revision' => 5,
                    'type' => 'notify',
                    'requiresDevAccess' => true,
                    'message' => ['type' => 'warning', 'text' => 'Updated', 'markdown' => false],
                ],
                ['revision' => 6, 'type' => 'refresh', 'requiresDevAccess' => false],
            ],
        ];
        $snapshot = LocalModelsSnapshot::fromArray($data);

        expect($snapshot->state)->toBeInstanceOf(LocalModelsView::class)
            ->and($snapshot->state->machine)->toBeInstanceOf(LocalModelMachineView::class)
            ->and($snapshot->state->machine->rows[0])->toBeInstanceOf(LocalModelMachineRow::class)
            ->and($snapshot->state->machine->rows[0]->descriptor)->toBeInstanceOf(LocalModelDescriptor::class)
            ->and($snapshot->state->machine->rows[0]->disk->kind)->toBe('ok')
            ->and($snapshot->state->downloads['model-1']->receivedBytes)->toBe(10)
            ->and($snapshot->state->operations['model-1'])->toBe(LocalModelOperation::Downloading)
            ->and($snapshot->state->operations['future-model'])->toBe('future-operation')
            ->and($snapshot->state->notices['model-1']->text)->toBe('Ready')
            ->and($snapshot->events[0])->toBeInstanceOf(LocalModelsEvent::class)
            ->and($snapshot->events[0]->message->markdown)->toBeFalse()
            ->and($snapshot->events[1]->message)->toBeNull()
            ->and($snapshot->toArray())->toBe($data);
    });

    it('round trips local model request types and omits null configure values', function () {
        $model = LocalModelDescriptor::fromArray([
            'id' => 'model-1',
            'displayName' => 'Model One',
            'azureMl' => ['registry' => 'azureml', 'name' => 'model-one', 'version' => '1'],
            'source' => ['id' => 'azureml', 'label' => 'Azure ML', 'ordering' => 1],
            'fileName' => 'model.gguf',
            'provider' => 'Publisher',
            'license' => 'MIT',
            'sourceLabel' => 'Azure ML',
            'runtime' => 'llama.cpp',
            'providerName' => 'local',
            'serveOptions' => [],
            'limits' => ['maxPromptTokens' => 100, 'maxContextWindowTokens' => 200, 'maxOutputTokens' => 50],
            'supportsToolCalls' => false,
            'metadata' => [],
        ]);
        $requests = [
            [new LocalModelsConfigureRequest(true, true, false, false, sessionId: 'session-1'), [
                'enabled' => true,
                'catalogEnabled' => true,
                'includeDev' => false,
                'streamerMode' => false,
                'sessionId' => 'session-1',
            ], LocalModelsConfigureRequest::class],
            [new LocalModelsWatchRequest(7), ['afterRevision' => 7], LocalModelsWatchRequest::class],
            [new LocalModelsRefreshRequest(false), ['background' => false], LocalModelsRefreshRequest::class],
            [new LocalModelsSetupRequest($model, 'repair'), ['model' => $model->toArray(), 'intent' => 'repair'], LocalModelsSetupRequest::class],
            [new LocalModelsModelRequest($model), ['model' => $model->toArray()], LocalModelsModelRequest::class],
            [new LocalModelsModelIdRequest('model-1'), ['modelId' => 'model-1'], LocalModelsModelIdRequest::class],
        ];

        foreach ($requests as [$request, $expected, $type]) {
            expect($request->toArray())->toBe($expected)
                ->and($type::fromArray($expected)->toArray())->toBe($expected);
        }

        expect((new LocalModelsConfigureRequest(true, false, false, false, sessionId: null))->toArray())
            ->toBe([
                'enabled' => true,
                'catalogEnabled' => false,
                'includeDev' => false,
                'streamerMode' => false,
            ]);
    });
});
