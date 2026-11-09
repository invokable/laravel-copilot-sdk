<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelProviderAdapterCatalog;
use Revolution\Copilot\Types\Rpc\ModelProviderAdapterDescriptor;
use Revolution\Copilot\Types\Rpc\ModelProviderAdapterOperationDescriptor;
use Revolution\Copilot\Types\Rpc\ModelProviderAttribution;
use Revolution\Copilot\Types\Rpc\ModelProviderAutomaticDiscoveryPolicy;

describe('ModelProviderAdapterCatalog', function () {
    it('defaults to an empty provider list', function () {
        $catalog = ModelProviderAdapterCatalog::fromArray([]);

        expect($catalog->providers)->toBe([])
            ->and($catalog->toArray())->toBe(['providers' => []]);
    });

    it('maps provider descriptors and round-trips nested values', function () {
        $data = [
            'providers' => [
                [
                    'adapterId' => 'openai-compatible',
                    'displayName' => 'OpenAI Compatible',
                    'providerKind' => 'openai',
                    'automaticDiscovery' => [
                        'mode' => 'manual',
                        'networkScope' => 'user-configured',
                        'requiresInput' => true,
                        'requiresTrust' => false,
                    ],
                    'operations' => [
                        ['name' => 'listModels', 'inputSchema' => ['type' => 'object']],
                    ],
                    'provenance' => [
                        'source' => 'user',
                        'ownerId' => 'user-123',
                        'ownerDisplayName' => 'Example User',
                    ],
                ],
            ],
        ];

        $catalog = ModelProviderAdapterCatalog::fromArray($data);
        $provider = $catalog->providers[0];

        expect($provider)->toBeInstanceOf(ModelProviderAdapterDescriptor::class)
            ->and($provider->adapterId)->toBe('openai-compatible')
            ->and($provider->automaticDiscovery)->toBeInstanceOf(ModelProviderAutomaticDiscoveryPolicy::class)
            ->and($provider->automaticDiscovery->requiresInput)->toBeTrue()
            ->and($provider->operations[0])->toBeInstanceOf(ModelProviderAdapterOperationDescriptor::class)
            ->and($provider->operations[0]->inputSchema)->toBe(['type' => 'object'])
            ->and($provider->provenance)->toBeInstanceOf(ModelProviderAttribution::class)
            ->and($provider->provenance->ownerDisplayName)->toBe('Example User')
            ->and($catalog->toArray())->toBe($data);
    });
});
