<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ExtensionSource;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProfile;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveRequest;
use Revolution\Copilot\Types\Rpc\ExtensionLaunchProviderResolveResult;

describe('Extension launch provider types', function () {
    it('round trips extension resolve requests and source values', function () {
        $request = ExtensionLaunchProviderResolveRequest::fromArray([
            'id' => 'project:example',
            'name' => 'example',
            'modulePath' => '/extensions/example/index.js',
            'source' => 'project',
        ]);

        expect($request->source)->toBe(ExtensionSource::PROJECT)
            ->and($request->toArray())->toBe([
                'id' => 'project:example',
                'name' => 'example',
                'modulePath' => '/extensions/example/index.js',
                'source' => 'project',
            ]);
    });

    it('round trips launch profiles and provider results', function () {
        $result = ExtensionLaunchProviderResolveResult::fromArray([
            'launch' => [
                'executable' => '/usr/bin/node',
                'args' => ['/extensions/example/index.js'],
                'env' => ['NODE_ENV' => 'production'],
            ],
        ]);

        expect($result->launch)->toBeInstanceOf(ExtensionLaunchProfile::class)
            ->and($result->toArray())->toBe([
                'launch' => [
                    'executable' => '/usr/bin/node',
                    'args' => ['/extensions/example/index.js'],
                    'env' => ['NODE_ENV' => 'production'],
                ],
            ]);
    });

    it('omits the launch key when no profile is supplied', function () {
        expect(ExtensionLaunchProviderResolveResult::fromArray([])->toArray())->toBe([]);
    });
});
