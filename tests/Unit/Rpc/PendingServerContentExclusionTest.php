<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerContentExclusion;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\ContentExclusionValidatePoliciesRequest;
use Revolution\Copilot\Types\Rpc\ContentExclusionValidatePoliciesResult;

it('validates raw policies without a session and returns normalized policies', function () {
    $policiesJson = '{ "policies" : [ {"path":"vendor/**"} ] }';
    $normalizedPolicies = [
        ['pattern' => 'vendor/**', 'source' => 'user'],
    ];
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('contentExclusion.validatePolicies', ['policiesJson' => $policiesJson])
        ->andReturn(['policies' => $normalizedPolicies]);

    $pending = (new ServerRpc($client))->contentExclusion();
    $result = $pending->validatePolicies(new ContentExclusionValidatePoliciesRequest($policiesJson));

    expect($pending)->toBeInstanceOf(PendingServerContentExclusion::class)
        ->and($result)->toBeInstanceOf(ContentExclusionValidatePoliciesResult::class)
        ->and($result->policies)->toBe($normalizedPolicies);
});

it('propagates runtime RPC errors for invalid policies', function () {
    $client = Mockery::mock(JsonRpcClient::class);
    $client->shouldReceive('request')
        ->once()
        ->with('contentExclusion.validatePolicies', ['policiesJson' => '{ invalid json }'])
        ->andThrow(new JsonRpcException(-32602, 'Invalid content-exclusion policies'));

    $validate = fn () => (new ServerRpc($client))->contentExclusion()->validatePolicies([
        'policiesJson' => '{ invalid json }',
    ]);

    expect($validate)->toThrow(JsonRpcException::class, 'Invalid content-exclusion policies');
});
