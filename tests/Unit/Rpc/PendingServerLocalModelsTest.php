<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerLocalModels;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\LocalModelsConfigureRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsConfigureResult;
use Revolution\Copilot\Types\Rpc\LocalModelsInspectResult;
use Revolution\Copilot\Types\Rpc\LocalModelsPrepareResult;
use Revolution\Copilot\Types\Rpc\LocalModelsWatchRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsWatchResult;

describe('PendingServerLocalModels', function () {
    it('sends all LocalModels operations with their wire parameters', function () {
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
            'supportsToolCalls' => true,
            'metadata' => [],
        ];
        $state = [
            'enabled' => true,
            'machine' => ['modelsDirectory' => '/models', 'rows' => []],
            'downloads' => [],
            'operations' => [],
            'notices' => [],
            'catalogLoading' => false,
            'hideDevModels' => false,
            'hideWinMlModels' => false,
            'prerequisiteNotice' => '',
        ];
        $snapshot = ['revision' => 2, 'state' => $state, 'events' => []];
        $cases = [
            ['configure', 'localModels.configure', [
                'enabled' => true,
                'catalogEnabled' => true,
                'includeDev' => false,
                'streamerMode' => false,
            ], $snapshot, LocalModelsConfigureResult::class],
            ['inspect', 'localModels.inspect', [], $snapshot, LocalModelsInspectResult::class],
            ['watch', 'localModels.watch', ['afterRevision' => 1], $snapshot, LocalModelsWatchResult::class],
            ['refresh', 'localModels.refresh', ['background' => false], null, null],
            ['prefetch', 'localModels.prefetch', [], null, null],
            ['prepare', 'localModels.prepare', ['model' => $descriptor], $descriptor, LocalModelsPrepareResult::class],
            ['setup', 'localModels.setup', ['model' => $descriptor, 'intent' => 'download'], null, null],
            ['deleteModel', 'localModels.deleteModel', ['model' => $descriptor], null, null],
            ['cancel', 'localModels.cancel', ['modelId' => 'model-1'], null, null],
            ['cancelAll', 'localModels.cancelAll', [], null, null],
            ['dismissNotice', 'localModels.dismissNotice', ['modelId' => 'model-1'], null, null],
        ];
        $client = Mockery::mock(JsonRpcClient::class);

        foreach ($cases as [$method, $rpcMethod, $params, $response, $resultType]) {
            $expectation = $client->shouldReceive('request')->once();

            if ($method === 'watch') {
                $expectation->with($rpcMethod, $params, null, null);
            } else {
                $expectation->with($rpcMethod, $params);
            }

            $expectation->andReturn($response);
        }

        $serverRpc = new ServerRpc($client);
        $localModels = $serverRpc->localModels();

        expect($localModels)->toBeInstanceOf(PendingServerLocalModels::class);

        foreach ($cases as [$method, , $params, $response, $resultType]) {
            $result = in_array($method, ['inspect', 'prefetch', 'cancelAll'], true)
                ? $localModels->{$method}()
                : $localModels->{$method}($params);

            if ($resultType === null) {
                expect($result)->toBeNull();

                continue;
            }

            expect($result)->toBeInstanceOf($resultType)
                ->and($result->toArray())->toBe($response);
        }
    });

    it('sends watch revision and the cancellation signal without a timeout', function () {
        $signal = new CancellationToken;
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('localModels.watch', ['afterRevision' => 42], null, $signal)
            ->andReturn([
                'revision' => 43,
                'state' => [
                    'enabled' => false,
                    'machine' => ['modelsDirectory' => '/models', 'rows' => []],
                    'downloads' => [],
                    'operations' => [],
                    'notices' => [],
                    'catalogLoading' => false,
                    'hideDevModels' => true,
                    'hideWinMlModels' => true,
                    'prerequisiteNotice' => 'Unavailable',
                ],
                'events' => [],
            ]);

        $result = (new PendingServerLocalModels($client))->watch(
            new LocalModelsWatchRequest(afterRevision: 42),
            signal: $signal,
        );

        expect($result)->toBeInstanceOf(LocalModelsWatchResult::class)
            ->and($result->revision)->toBe(43);
    });

    it('omits a null session id when configuring the local model manager', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('localModels.configure', [
                'enabled' => true,
                'catalogEnabled' => false,
                'includeDev' => false,
                'streamerMode' => false,
            ])
            ->andReturn([
                'revision' => 1,
                'state' => [
                    'enabled' => true,
                    'machine' => ['modelsDirectory' => '/models', 'rows' => []],
                    'downloads' => [],
                    'operations' => [],
                    'notices' => [],
                    'catalogLoading' => false,
                    'hideDevModels' => false,
                    'hideWinMlModels' => false,
                    'prerequisiteNotice' => '',
                ],
                'events' => [],
            ]);

        $result = (new PendingServerLocalModels($client))->configure(
            new LocalModelsConfigureRequest(
                enabled: true,
                catalogEnabled: false,
                includeDev: false,
                streamerMode: false,
                sessionId: null,
            ),
        );

        expect($result->revision)->toBe(1);
    });
});
