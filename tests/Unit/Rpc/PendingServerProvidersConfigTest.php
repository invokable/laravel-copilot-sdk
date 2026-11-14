<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ProviderConfigTransport;
use Revolution\Copilot\Enums\SavedProviderCredentialStatus;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\ProviderModelConfig;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigReadResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigRemoveModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigRemoveModelResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateModelResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateProviderRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateProviderResult;
use Revolution\Copilot\Types\Rpc\SavedProviderConfigurationProvider;
use Revolution\Copilot\Types\Rpc\SavedProviderConfigurationProviderInput;

describe('PendingServerProvidersConfig', function () {
    it('reads saved providers and models without exposing secrets', function () {
        $payload = [
            'providers' => [
                ['provider' => ['name' => 'example', 'baseUrl' => 'https://example.test'], 'credentialStatus' => 'present'],
                ['provider' => ['name' => 'local', 'baseUrl' => 'http://localhost'], 'credentialStatus' => 'absent'],
                ['provider' => ['name' => 'offline', 'baseUrl' => 'https://example.test'], 'credentialStatus' => 'unavailable'],
                ['provider' => ['name' => 'future', 'baseUrl' => 'https://example.test'], 'credentialStatus' => 'future-status'],
            ],
            'models' => [
                ['id' => 'one', 'provider' => 'example', 'maxOutputTokens' => 100],
                ['id' => 'two', 'provider' => 'local'],
            ],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with('providers.config.read')->andReturn($payload);

        $result = (new ServerRpc($client))->providersConfig()->read();

        expect($result)->toBeInstanceOf(ProvidersConfigReadResult::class)
            ->and($result->providers[0])->toBeInstanceOf(SavedProviderConfigurationProvider::class)
            ->and($result->providers[0]->provider)->toBeInstanceOf(SavedProviderConfigurationProviderInput::class)
            ->and($result->providers[0]->credentialStatus)->toBe(SavedProviderCredentialStatus::Present)
            ->and($result->providers[1]->credentialStatus)->toBe(SavedProviderCredentialStatus::Absent)
            ->and($result->providers[2]->credentialStatus)->toBe(SavedProviderCredentialStatus::Unavailable)
            ->and($result->providers[3]->credentialStatus)->toBe('future-status')
            ->and($result->models[0])->toBeInstanceOf(ProviderModelConfig::class)
            ->and($result->toArray())->toBe($payload)
            ->and(ProvidersConfigReadResult::fromArray(['providers' => $result->providers, 'models' => $result->models])->toArray())->toBe($payload)
            ->and(ProvidersConfigReadResult::fromArray([])->toArray())->toBe(['providers' => [], 'models' => []]);
    });

    it('updates provider settings with typed or array input and an optional credential', function () {
        $provider = new SavedProviderConfigurationProviderInput(name: 'example', baseUrl: 'https://example.test');
        $request = new ProvidersConfigUpdateProviderRequest($provider, 'example-credential');
        $payload = ['provider' => $provider->toArray(), 'credential' => 'example-credential'];
        $withoutCredential = ['provider' => $provider->toArray()];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with('providers.config.updateProvider', $payload)
            ->andReturn(['providerName' => 'example']);
        $client->shouldReceive('request')->once()->with('providers.config.updateProvider', $withoutCredential)
            ->andReturn(['providerName' => 'example']);

        $rpc = (new ServerRpc($client))->providersConfig();
        $result = $rpc->updateProvider($request);
        $arrayResult = $rpc->updateProvider($withoutCredential);

        expect($result)->toBeInstanceOf(ProvidersConfigUpdateProviderResult::class)
            ->and($result->toArray())->toBe(['providerName' => 'example'])
            ->and($arrayResult->providerName)->toBe('example')
            ->and(ProvidersConfigUpdateProviderRequest::fromArray($payload)->toArray())->toBe($payload)
            ->and(ProvidersConfigUpdateProviderRequest::fromArray(['provider' => $provider])->toArray())->toBe($withoutCredential)
            ->and((new ReflectionMethod($rpc, 'updateProvider'))->getParameters()[0]->getAttributes(SensitiveParameter::class))->toHaveCount(1)
            ->and((new ReflectionMethod(ProvidersConfigUpdateProviderRequest::class, '__construct'))->getParameters()[1]->getAttributes(SensitiveParameter::class))->toHaveCount(1)
            ->and((new ReflectionMethod(ProvidersConfigUpdateProviderRequest::class, 'fromArray'))->getParameters()[0]->getAttributes(SensitiveParameter::class))->toHaveCount(1);
    });

    it('updates a model with typed or array input', function () {
        $model = new ProviderModelConfig(id: 'model', provider: 'example', maxOutputTokens: 100);
        $payload = ['model' => $model->toArray()];
        $response = ['providerName' => 'example', 'modelId' => 'model'];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->twice()->with('providers.config.updateModel', $payload)->andReturn($response);

        $rpc = (new ServerRpc($client))->providersConfig();
        $result = $rpc->updateModel(new ProvidersConfigUpdateModelRequest($model));

        expect($result)->toBeInstanceOf(ProvidersConfigUpdateModelResult::class)
            ->and($result->toArray())->toBe($response)
            ->and($rpc->updateModel($payload)->toArray())->toBe($response)
            ->and(ProvidersConfigUpdateModelRequest::fromArray($payload)->toArray())->toBe($payload)
            ->and(ProvidersConfigUpdateModelRequest::fromArray(['model' => $model])->toArray())->toBe($payload);
    });

    it('removes a model and preserves both provider removal results', function () {
        $payload = ['providerName' => 'example', 'modelId' => 'model'];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->twice()->with('providers.config.removeModel', $payload)
            ->andReturn(['providerRemoved' => true], ['providerRemoved' => false]);

        $rpc = (new ServerRpc($client))->providersConfig();
        $result = $rpc->removeModel(new ProvidersConfigRemoveModelRequest('example', 'model'));

        expect($result)->toBeInstanceOf(ProvidersConfigRemoveModelResult::class)
            ->and($result->toArray())->toBe(['providerRemoved' => true])
            ->and($rpc->removeModel($payload)->toArray())->toBe(['providerRemoved' => false])
            ->and(ProvidersConfigRemoveModelRequest::fromArray($payload)->toArray())->toBe($payload);
    });

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
