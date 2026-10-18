<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ManagedSettingMeta;
use Revolution\Copilot\Types\Rpc\ManagedSettingsComposeLayer;
use Revolution\Copilot\Types\Rpc\ManagedSettingsComposeRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsDiagnostic;
use Revolution\Copilot\Types\Rpc\ManagedSettingsLayer;
use Revolution\Copilot\Types\Rpc\ManagedSettingsMeta;
use Revolution\Copilot\Types\Rpc\ManagedSettingsResolveRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsValues;

it('round trips typed managed-settings composition data', function () {
    $request = ManagedSettingsComposeRequest::fromArray([
        'layers' => [
            ['source' => 'device', 'settings' => ['model' => 'gpt-5']],
        ],
    ]);
    $values = ManagedSettingsValues::fromArray([
        'model' => 'gpt-5',
        'autoTier' => 'balance',
        'contextTier' => 'long_context',
        'effortLevel' => 'high',
    ]);
    $meta = ManagedSettingsMeta::fromArray([
        'model' => ['overridable' => false, 'source' => 'device'],
        'contextTier' => ['overridable' => true, 'source' => 'policy', 'requested' => 'long_context'],
        'effortLevel' => ['overridable' => false, 'source' => 'device'],
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
        ->and($values->toArray()['contextTier'])->toBe('long_context')
        ->and($values->toArray()['effortLevel'])->toBe('high')
        ->and($meta->model)->toBeInstanceOf(ManagedSettingMeta::class)
        ->and($meta->toArray()['model']['overridable'])->toBeFalse()
        ->and($meta->toArray()['contextTier']['requested'])->toBe('long_context')
        ->and($meta->effortLevel)->toBeInstanceOf(ManagedSettingMeta::class)
        ->and($layer->toArray()['settings']['model']['value'])->toBe('gpt-5')
        ->and($diagnostic->toArray()['severity'])->toBe('warning');
});

it('includes the working directory when resolving organization managed settings', function () {
    expect(ManagedSettingsResolveRequest::fromArray([
        'selectionId' => 'selection-1',
        'workingDirectory' => '/workspace/project',
    ])->toArray())->toBe([
        'selectionId' => 'selection-1',
        'workingDirectory' => '/workspace/project',
    ]);
});

it('rejects malformed managed-settings auto tiers', function () {
    expect(fn () => ManagedSettingsValues::fromArray(['autoTier' => 42]))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects non-string context tiers and effort levels in managed settings', function () {
    expect(fn () => ManagedSettingsValues::fromArray(['contextTier' => 1]))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => ManagedSettingsValues::fromArray(['effortLevel' => false]))
        ->toThrow(InvalidArgumentException::class);
});
