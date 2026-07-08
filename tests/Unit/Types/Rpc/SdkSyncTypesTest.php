<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\Verbosity;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;
use Revolution\Copilot\Types\Rpc\PluginsReloadRequest;
use Revolution\Copilot\Types\SessionConfig;

describe('ModelSwitchToRequest verbosity', function () {
    it('can be created with verbosity enum', function () {
        $params = new ModelSwitchToRequest(
            modelId: 'gpt-4',
            verbosity: Verbosity::HIGH,
        );
        expect($params->toArray())->toBe([
            'modelId' => 'gpt-4',
            'verbosity' => 'high',
        ]);
    });

    it('can be created with verbosity string', function () {
        $params = new ModelSwitchToRequest(
            modelId: 'gpt-4',
            verbosity: 'low',
        );
        expect($params->toArray())->toBe([
            'modelId' => 'gpt-4',
            'verbosity' => 'low',
        ]);
    });

    it('filters null verbosity', function () {
        $params = new ModelSwitchToRequest(modelId: 'gpt-4', verbosity: null);
        expect($params->toArray())->not->toHaveKey('verbosity');
    });

    it('can be created from array with verbosity', function () {
        $params = ModelSwitchToRequest::fromArray([
            'modelId' => 'gpt-4',
            'verbosity' => 'medium',
        ]);
        expect($params->verbosity)->toBe('medium');
    });
});

describe('PluginsReloadRequest reloadExtensions', function () {
    it('can be created with reloadExtensions', function () {
        $req = new PluginsReloadRequest(reloadExtensions: true);
        expect($req->toArray())->toBe(['reloadExtensions' => true]);
    });

    it('filters null reloadExtensions', function () {
        $req = new PluginsReloadRequest(reloadHooks: true);
        expect($req->toArray())->not->toHaveKey('reloadExtensions')
            ->and($req->toArray())->toHaveKey('reloadHooks');
    });

    it('can be created from array with reloadExtensions', function () {
        $req = PluginsReloadRequest::fromArray([
            'reloadExtensions' => false,
            'reloadHooks' => true,
        ]);
        expect($req->reloadExtensions)->toBeFalse()
            ->and($req->reloadHooks)->toBeTrue();
    });

    it('defaults reloadExtensions to null', function () {
        $req = new PluginsReloadRequest();
        expect($req->reloadExtensions)->toBeNull();
    });
});

describe('SessionConfig verbosity and enableManagedSettings', function () {
    it('can be created with verbosity enum', function () {
        $config = new SessionConfig(verbosity: Verbosity::MEDIUM);
        $arr = $config->toArray();
        expect($arr)->toHaveKey('verbosity', 'medium');
    });

    it('can be created with verbosity string', function () {
        $config = new SessionConfig(verbosity: 'high');
        $arr = $config->toArray();
        expect($arr)->toHaveKey('verbosity', 'high');
    });

    it('can be created with enableManagedSettings', function () {
        $config = new SessionConfig(enableManagedSettings: true);
        $arr = $config->toArray();
        expect($arr)->toHaveKey('enableManagedSettings', true);
    });

    it('filters null verbosity and enableManagedSettings', function () {
        $config = new SessionConfig(model: 'gpt-4');
        $arr = $config->toArray();
        expect($arr)->not->toHaveKey('verbosity')
            ->and($arr)->not->toHaveKey('enableManagedSettings');
    });

    it('can be created from array with verbosity and enableManagedSettings', function () {
        $config = SessionConfig::fromArray([
            'verbosity' => 'low',
            'enableManagedSettings' => true,
        ]);
        expect($config->verbosity)->toBe('low')
            ->and($config->enableManagedSettings)->toBeTrue();
    });
});
