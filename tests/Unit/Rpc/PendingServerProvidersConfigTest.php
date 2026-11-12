<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ProviderConfigTransport;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\ProviderModelConfig;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelResult;
use Revolution\Copilot\Types\Rpc\SavedProviderConfigurationProviderInput;

describe('PendingServerProvidersConfig', function () {
    it('sends a typed request and hydrates a created provider result', function () {
        $provider = new SavedProviderConfigurationProviderInput(
            name: 'example',
            baseUrl: 'https://example.test/v1',
            type: 'azure',
            wireApi: 'responses',
            transport: ProviderConfigTransport::Websockets,
            localModel: ['enabled' => false],
            azure: ['apiVersion' => '2025-01-01'],
            modelProvider: 'azure_openai',
        );
        $model = new ProviderModelConfig(id: 'model', provider: 'example', maxOutputTokens: 100);
        $request = new ProvidersConfigAddModelRequest($provider, $model, 'example-credential');
        $payload = [
            'provider' => [
                'name' => 'example',
                'baseUrl' => 'https://example.test/v1',
                'type' => 'azure',
                'wireApi' => 'responses',
                'transport' => 'websockets',
                'localModel' => ['enabled' => false],
                'azure' => ['apiVersion' => '2025-01-01'],
                'modelProvider' => 'azure_openai',
            ],
            'model' => ['id' => 'model', 'provider' => 'example', 'maxOutputTokens' => 100],
            'credential' => 'example-credential',
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('providers.config.addModel', $payload)
            ->andReturn(['providerAdded' => true]);

        $result = (new ServerRpc($client))->providersConfig()->addModel($request);

        expect($result)->toBeInstanceOf(ProvidersConfigAddModelResult::class)
            ->and($result->providerAdded)->toBeTrue()
            ->and($result->toArray())->toBe(['providerAdded' => true])
            ->and(ProvidersConfigAddModelRequest::fromArray($payload)->toArray())->toBe($payload);
    });

    it('accepts array input without a credential and preserves false results', function () {
        $payload = [
            'provider' => ['name' => 'local', 'baseUrl' => 'http://localhost:11434'],
            'model' => ['id' => 'model', 'provider' => 'local'],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('providers.config.addModel', $payload)
            ->andReturn(['providerAdded' => false]);

        $result = (new ServerRpc($client))->providersConfig()->addModel($payload);

        expect($result->providerAdded)->toBeFalse()
            ->and($result->toArray())->toBe(['providerAdded' => false]);
    });

    it('preserves unknown provider values and nested typed objects', function () {
        $provider = new SavedProviderConfigurationProviderInput(
            name: 'future',
            baseUrl: 'https://example.test',
            type: 'future-type',
            wireApi: 'future-wire',
            transport: 'future-transport',
            localModel: false,
            modelProvider: 'future-provider',
        );
        $model = new ProviderModelConfig(id: 'model', provider: 'future');
        $request = ProvidersConfigAddModelRequest::fromArray(compact('provider', 'model'));

        expect(SavedProviderConfigurationProviderInput::fromArray($provider->toArray())->toArray())
            ->toBe($provider->toArray())
            ->and($request->toArray())->toBe([
                'provider' => $provider->toArray(),
                'model' => $model->toArray(),
            ]);
    });
});
