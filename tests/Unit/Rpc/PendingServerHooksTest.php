<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\HookOrigin;
use Revolution\Copilot\Enums\HookType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerHooks;
use Revolution\Copilot\Types\Rpc\HooksDiscoverRequest;
use Revolution\Copilot\Types\Rpc\HooksDiscoverResult;

describe('PendingServerHooks', function () {
    it('discovers hooks with typed request parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('hooks.discover', [
                'hookDirectories' => ['/hooks'],
                'projectPaths' => ['/workspace'],
                'excludeHostHooks' => false,
            ])
            ->andReturn([
                'hooks' => [[
                    'id' => 'hook-1',
                    'hookType' => 'preToolUse',
                    'origin' => 'repository',
                    'source' => '.github/hooks.json',
                    'projectPath' => '/workspace',
                    'enabled' => true,
                    'disableKey' => 'disable-key',
                ]],
                'warnings' => ['recoverable warning'],
                'errors' => [],
            ]);

        $result = (new PendingServerHooks($client))->discover(new HooksDiscoverRequest(
            hookDirectories: ['/hooks'],
            projectPaths: ['/workspace'],
            excludeHostHooks: false,
        ));

        expect($result)->toBeInstanceOf(HooksDiscoverResult::class)
            ->and($result->hooks[0]->hookType)->toBe(HookType::PreToolUse)
            ->and($result->hooks[0]->origin)->toBe(HookOrigin::Repository)
            ->and($result->warnings)->toBe(['recoverable warning']);
    });

    it('discovers hooks with array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('hooks.discover', ['projectPaths' => ['/workspace']])
            ->andReturn(['hooks' => [], 'warnings' => [], 'errors' => []]);

        $result = (new PendingServerHooks($client))->discover([
            'projectPaths' => ['/workspace'],
        ]);

        expect($result->hooks)->toBeEmpty()
            ->and($result->errors)->toBeEmpty();
    });
});
