<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ManagedPermissionVerdict;
use Revolution\Copilot\Types\Rpc\ManagedPermissionsContext;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateRequest;
use Revolution\Copilot\Types\Rpc\ManagedSettingsPermissionsEvaluateResult;

it('round trips managed permissions context and ordered evaluations', function () {
    $request = ManagedSettingsPermissionsEvaluateRequest::fromArray([
        'context' => [
            'failClosed' => true,
            'permissions' => ['url' => ['allow' => ['https://example.com']]],
        ],
        'operations' => [
            ['url' => 'https://example.com', 'kind' => 'url'],
            ['url' => 'https://blocked.example', 'kind' => 'url'],
        ],
    ]);
    $result = ManagedSettingsPermissionsEvaluateResult::fromArray([
        'failClosed' => true,
        'results' => [
            ['operation' => ['url' => 'https://example.com', 'kind' => 'url'], 'verdict' => 'allow'],
            ['operation' => ['url' => 'https://blocked.example', 'kind' => 'url'], 'verdict' => 'deny'],
        ],
    ]);

    expect($request->context)->toBeInstanceOf(ManagedPermissionsContext::class)
        ->and($request->toArray()['operations'])->toHaveCount(2)
        ->and($result->results[0]->verdict)->toBe(ManagedPermissionVerdict::ALLOW)
        ->and($result->results[1]->verdict)->toBe(ManagedPermissionVerdict::DENY)
        ->and($result->toArray()['failClosed'])->toBeTrue();
});
