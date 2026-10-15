<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessionContentExclusion;
use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsRequest;
use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsResult;
use Revolution\Copilot\Types\Rpc\ContentExclusionPathCheck;

describe('PendingSessionContentExclusion', function () {
    it('checks typed paths with the session ID and preserves ordered duplicate results', function () {
        $paths = ['/workspace/private.txt', '/workspace/public.txt', '/workspace/private.txt'];
        $checks = [
            ['path' => '/workspace/private.txt', 'excluded' => true],
            ['path' => '/workspace/public.txt', 'excluded' => false],
            ['path' => '/workspace/private.txt', 'excluded' => true],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.contentExclusion.checkPaths', [
                'paths' => $paths,
                'sessionId' => 'test-session',
            ])
            ->andReturn(['available' => true, 'checks' => $checks]);

        $pending = new PendingSessionContentExclusion($client, 'test-session');
        $result = $pending->checkPaths(new ContentExclusionCheckPathsRequest($paths));

        expect($result)->toBeInstanceOf(ContentExclusionCheckPathsResult::class)
            ->and($result->available)->toBeTrue()
            ->and($result->checks)->toHaveCount(3)
            ->and($result->checks[0])->toBeInstanceOf(ContentExclusionPathCheck::class)
            ->and($result->toArray())->toBe(['available' => true, 'checks' => $checks]);
    });

    it('accepts an array request and treats unavailable checks as excluded', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.contentExclusion.checkPaths', [
                'paths' => ['/workspace/private.txt'],
                'sessionId' => 'test-session',
            ])
            ->andReturn(['available' => false, 'checks' => []]);

        $result = (new PendingSessionContentExclusion($client, 'test-session'))->checkPaths([
            'paths' => ['/workspace/private.txt'],
        ]);

        expect($result->available)->toBeFalse()
            ->and($result->checks)->toBe([])
            ->and($result->isExcluded('/workspace/private.txt'))->toBeTrue();
    });
});
