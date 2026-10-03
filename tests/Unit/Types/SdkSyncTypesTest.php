<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ProviderConfig;
use Revolution\Copilot\Types\Rpc\AuthIdentityMetadata;
use Revolution\Copilot\Types\Rpc\ConnectorSessionAccount;
use Revolution\Copilot\Types\Rpc\ManagedSettingMeta;
use Revolution\Copilot\Types\Rpc\ManagedSettingsComposeLayer;
use Revolution\Copilot\Types\Rpc\ManagedSettingsComposeRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsDiagnostic;
use Revolution\Copilot\Types\Rpc\ManagedSettingsLayer;
use Revolution\Copilot\Types\Rpc\ManagedSettingsMeta;
use Revolution\Copilot\Types\Rpc\ManagedSettingsValues;
use Revolution\Copilot\Types\Rpc\McpPrompt;
use Revolution\Copilot\Types\Rpc\McpPromptArgument;
use Revolution\Copilot\Types\Rpc\McpPromptMessage;
use Revolution\Copilot\Types\TranscriptRecovery;

it('round trips provider model-provider metadata', function () {
    $config = ProviderConfig::fromArray([
        'baseUrl' => 'https://api.example.com',
        'modelProvider' => 'azure-openai',
    ]);

    expect($config->modelProvider)->toBe('azure-openai')
        ->and($config->toArray()['modelProvider'])->toBe('azure-openai');
});

it('round trips transcript recovery details', function () {
    $recovery = TranscriptRecovery::fromArray([
        'plannedBackupPath' => '/tmp/transcript.jsonl.bak',
        'invalidLineNumbers' => [2, 5],
        'sessionStartMoved' => true,
    ]);

    expect($recovery->toArray())->toBe([
        'plannedBackupPath' => '/tmp/transcript.jsonl.bak',
        'invalidLineNumbers' => [2, 5],
        'sessionStartMoved' => true,
    ]);
});

it('round trips nested MCP prompt and connector account data', function () {
    $prompt = McpPrompt::fromArray([
        'name' => 'summarize',
        'arguments' => [['name' => 'document', 'required' => true]],
    ]);
    $account = ConnectorSessionAccount::fromArray([
        'accountId' => 'opaque-account',
        'authInfo' => ['host' => 'github.com', 'login' => 'octocat'],
    ]);
    $message = McpPromptMessage::fromArray([
        'role' => 'user',
        'content' => ['type' => 'text', 'text' => 'Hello'],
    ]);

    expect($prompt->arguments[0])->toBeInstanceOf(McpPromptArgument::class)
        ->and($prompt->toArray()['arguments'][0]['required'])->toBeTrue()
        ->and($account->authInfo)->toBeInstanceOf(AuthIdentityMetadata::class)
        ->and($account->toArray()['authInfo']['login'])->toBe('octocat')
        ->and($message->toArray()['content']['text'])->toBe('Hello');
});

it('round trips typed managed-settings composition data', function () {
    $request = ManagedSettingsComposeRequest::fromArray([
        'layers' => [
            ['source' => 'device', 'settings' => ['model' => 'gpt-5']],
        ],
    ]);
    $values = ManagedSettingsValues::fromArray(['model' => 'gpt-5', 'autoTier' => 'balance']);
    $meta = ManagedSettingsMeta::fromArray([
        'model' => ['overridable' => false, 'source' => 'device'],
    ]);
    $layer = ManagedSettingsLayer::fromArray([
        'source' => 'device',
        'settings' => ['model' => ['value' => 'gpt-5', 'overridable' => false]],
    ]);
    $diagnostic = ManagedSettingsDiagnostic::fromArray([
        'path' => 'model',
        'severity' => 'warning',
        'message' => 'Unknown field ignored',
    ]);

    expect($request->layers[0])->toBeInstanceOf(ManagedSettingsComposeLayer::class)
        ->and($request->toArray()['layers'][0]['source'])->toBe('device')
        ->and($values->toArray()['autoTier'])->toBe('balance')
        ->and($meta->model)->toBeInstanceOf(ManagedSettingMeta::class)
        ->and($meta->toArray()['model']['overridable'])->toBeFalse()
        ->and($layer->toArray()['settings']['model']['value'])->toBe('gpt-5')
        ->and($diagnostic->toArray()['severity'])->toBe('warning');
});

it('rejects malformed managed-settings auto tiers', function () {
    expect(fn () => ManagedSettingsValues::fromArray(['autoTier' => 42]))
        ->toThrow(InvalidArgumentException::class);
});
