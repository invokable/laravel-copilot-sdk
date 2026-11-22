<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\MarketplaceCheckUpdateResult;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceCheckUpdateRequest;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceLiveState;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchEntry;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchError;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchRequest;
use Revolution\Copilot\Types\Rpc\PluginMarketplaceSearchResult;

it('round trips the marketplace update check request and result', function () {
    $requestData = ['name' => 'official'];
    $resultData = [
        'updateAvailable' => false,
        'installedVersion' => '1.0.0',
        'latestVersion' => '1.0.0',
        'notFetched' => false,
        'error' => 'No update is available.',
    ];

    expect(PluginMarketplaceCheckUpdateRequest::fromArray($requestData)->toArray())->toBe($requestData)
        ->and(MarketplaceCheckUpdateResult::fromArray($resultData)->toArray())->toBe($resultData);
});

it('round trips marketplace search requests and preserves a false force refresh flag', function () {
    $data = ['query' => 'lint', 'forceRefresh' => false];

    $request = PluginMarketplaceSearchRequest::fromArray($data);

    expect($request->forceRefresh)->toBeFalse()
        ->and($request->toArray())->toBe($data)
        ->and(PluginMarketplaceSearchRequest::fromArray(['query' => 'lint'])->toArray())->toBe(['query' => 'lint']);
});

it('round trips marketplace search results with typed plugins, live state, and errors', function () {
    $data = [
        'plugins' => [[
            'name' => 'eslint',
            'marketplace' => 'official',
            'installSpec' => 'eslint@official',
            'installed' => true,
            'live' => [
                'enabled' => false,
                'installedFrom' => 'official',
            ],
            'description' => 'Lint JavaScript files',
            'version' => '9.0.0',
            'sourceUrl' => 'https://example.test/eslint',
            'metadata' => [
                'tags' => ['lint', 'javascript'],
                'featured' => true,
                'details' => ['score' => 4.5, 'deprecated' => null],
            ],
        ], [
            'name' => 'prettier',
            'marketplace' => 'team',
            'installSpec' => 'prettier@team',
            'installed' => false,
            'metadata' => null,
        ]],
        'errors' => [[
            'marketplace' => 'unavailable',
            'error' => 'Timed out',
            'scope' => 'workspace',
        ]],
    ];

    $result = PluginMarketplaceSearchResult::fromArray($data);

    expect($result->plugins[0])->toBeInstanceOf(PluginMarketplaceSearchEntry::class)
        ->and($result->plugins[0]->live)->toBeInstanceOf(PluginMarketplaceLiveState::class)
        ->and($result->plugins[0]->live->enabled)->toBeFalse()
        ->and($result->plugins[0]->description)->toBe('Lint JavaScript files')
        ->and($result->plugins[0]->version)->toBe('9.0.0')
        ->and($result->plugins[0]->sourceUrl)->toBe('https://example.test/eslint')
        ->and($result->plugins[0]->metadata)->toBe($data['plugins'][0]['metadata'])
        ->and($result->plugins[1]->live)->toBeNull()
        ->and($result->plugins[1]->metadata)->toBeNull()
        ->and($result->errors[0])->toBeInstanceOf(PluginMarketplaceSearchError::class)
        ->and($result->toArray())->toBe($data);
});
