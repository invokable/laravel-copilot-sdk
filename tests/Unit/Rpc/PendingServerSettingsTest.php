<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSettings;
use Revolution\Copilot\Types\Rpc\SettingsRedactValueForDisplayRequest;
use Revolution\Copilot\Types\Rpc\SettingsRedactValueForDisplayResult;
use Revolution\Copilot\Types\Rpc\SettingsRemoveRawKeyRequest;
use Revolution\Copilot\Types\Rpc\SettingsRemoveRawKeyResult;

describe('PendingServerSettings', function () {
    it('removes a raw key from a settings file', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('settings.removeRawKey', [
                'filePath' => '/home/user/.copilot/settings.json',
                'rawKey' => 'misspelledKey',
            ])
            ->andReturn(['status' => 'ok']);

        $result = (new PendingServerSettings($client))->removeRawKey(new SettingsRemoveRawKeyRequest(
            filePath: '/home/user/.copilot/settings.json',
            rawKey: 'misspelledKey',
        ));

        expect($result)->toBeInstanceOf(SettingsRemoveRawKeyResult::class)
            ->and($result->status)->toBe('ok');
    });

    it('accepts an array for removeRawKey and reports an invalid file', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('settings.removeRawKey', [
                'filePath' => '/tmp/broken-settings.json',
                'rawKey' => 'staleKey',
            ])
            ->andReturn(['status' => 'invalid']);

        $result = (new PendingServerSettings($client))->removeRawKey([
            'filePath' => '/tmp/broken-settings.json',
            'rawKey' => 'staleKey',
        ]);

        expect($result->status)->toBe('invalid');
    });

    it('redacts a settings value for display', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('settings.redactValueForDisplay', [
                'value' => ['apiKey' => 'secret-value', 'name' => 'visible'],
                'basePath' => ['providers', 'openai'],
            ])
            ->andReturn([
                'value' => ['apiKey' => '[redacted]', 'name' => 'visible'],
            ]);

        $result = (new PendingServerSettings($client))->redactValueForDisplay(new SettingsRedactValueForDisplayRequest(
            value: ['apiKey' => 'secret-value', 'name' => 'visible'],
            basePath: ['providers', 'openai'],
        ));

        expect($result)->toBeInstanceOf(SettingsRedactValueForDisplayResult::class)
            ->and($result->value)->toBe(['apiKey' => '[redacted]', 'name' => 'visible']);
    });

    it('accepts an array for redactValueForDisplay', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('settings.redactValueForDisplay', [
                'value' => 'plain',
                'basePath' => [],
            ])
            ->andReturn(['value' => 'plain']);

        $result = (new PendingServerSettings($client))->redactValueForDisplay([
            'value' => 'plain',
            'basePath' => [],
        ]);

        expect($result->value)->toBe('plain');
    });
});
