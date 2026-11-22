<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionPluginsCheckUpdateRequest;
use Revolution\Copilot\Types\Rpc\SessionPluginsCheckUpdateResult;

it('round trips the plugin update check request', function () {
    $data = ['name' => 'eslint', 'marketplace' => 'official'];

    $request = SessionPluginsCheckUpdateRequest::fromArray($data);

    expect($request->toArray())->toBe($data)
        ->and((new SessionPluginsCheckUpdateRequest('eslint', 'official'))->toArray())->toBe($data);
});

it('round trips the plugin update check result and preserves false values', function () {
    $data = [
        'updateAvailable' => false,
        'installedVersion' => '1.0.0',
        'latestVersion' => '1.0.0',
        'notFetched' => false,
        'error' => 'No update is available.',
    ];

    $result = SessionPluginsCheckUpdateResult::fromArray($data);

    expect($result->updateAvailable)->toBeFalse()
        ->and($result->installedVersion)->toBe('1.0.0')
        ->and($result->latestVersion)->toBe('1.0.0')
        ->and($result->notFetched)->toBeFalse()
        ->and($result->error)->toBe('No update is available.')
        ->and($result->toArray())->toBe($data);
});

it('omits absent optional fields from the plugin update check result', function () {
    $data = ['updateAvailable' => false];

    $result = SessionPluginsCheckUpdateResult::fromArray($data);

    expect($result->installedVersion)->toBeNull()
        ->and($result->latestVersion)->toBeNull()
        ->and($result->notFetched)->toBeNull()
        ->and($result->error)->toBeNull()
        ->and($result->toArray())->toBe($data);
});
