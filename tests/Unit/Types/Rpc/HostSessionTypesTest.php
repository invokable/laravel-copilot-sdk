<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\HostCreateSessionRequest;
use Revolution\Copilot\Types\Rpc\HostAuthentication;
use Revolution\Copilot\Types\Rpc\HostAuthenticationCancelledNotification;
use Revolution\Copilot\Types\Rpc\HostGitHubEnvironmentOptions;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;
use Revolution\Copilot\Types\Rpc\HostMaterializeSessionRequest;
use Revolution\Copilot\Types\Rpc\HostSessionAuthentication;
use Revolution\Copilot\Types\Rpc\HostSessionReleasedNotification;
use Revolution\Copilot\Types\Rpc\HostSetSessionCreationPolicyRequest;
use Revolution\Copilot\Types\Rpc\HostStartRequest;
use Revolution\Copilot\Types\Rpc\HostValidateAuthenticationRequest;
use Revolution\Copilot\Enums\HostAuthenticationMode;

it('round trips the advertised host session catalog', function () {
    $request = HostListSessionsRequest::fromArray(['hostId' => 'host-1']);
    $result = HostListSessionsResult::fromArray([
        'sessions' => [[
            'resource' => 'ahp://host/session-1',
            'title' => 'Review',
            'createdAt' => '2026-10-09T10:00:00Z',
            'modifiedAt' => '2026-10-09T10:15:00Z',
            'status' => 0x80000000,
            'activity' => 'Working',
        ]],
    ]);

    expect($request->toArray())->toBe(['hostId' => 'host-1'])
        ->and($result->sessions[0]->status)->toBe(0x80000000)
        ->and($result->toArray()['sessions'][0]['activity'])->toBe('Working');
});

it('round trips host configuration, connection binding, and resident preference', function () {
    $start = HostStartRequest::fromArray([
        'hostId' => 'host-1',
        'computeId' => 'compute-1',
        'githubEnvironment' => [
            'name' => 'Example',
            'computeId' => 'compute-1',
            'requireConnectionBinding' => false,
        ],
    ]);
    $handoff = HostCreateSessionRequest::fromArray([
        'handoffId' => 'handoff-1',
        'resume' => true,
        'preferResident' => true,
        'config' => ['sessionId' => 'session-1'],
    ]);

    expect($start->githubEnvironment)->toBeInstanceOf(HostGitHubEnvironmentOptions::class)
        ->and($start->toArray()['computeId'])->toBe('compute-1')
        ->and($start->toArray()['githubEnvironment']['requireConnectionBinding'])->toBeFalse()
        ->and($handoff->toArray()['preferResident'])->toBeTrue()
        ->and($handoff->toArray()['config']['sessionId'])->toBe('session-1');
});

it('round trips AHP authentication, cancellation, and session policy types', function () {
    $authentication = HostAuthentication::fromArray([
        'mode' => 'custom',
        'requireIdentity' => 'tenant-1',
    ]);
    $validated = HostSessionAuthentication::fromArray([
        'mode' => 'custom',
        'identity' => 'tenant-1',
    ]);
    $request = HostValidateAuthenticationRequest::fromArray([
        'hostId' => 'host-1',
        'requestId' => 'request-1',
        'token' => 'admission-token',
    ]);
    $materialize = HostMaterializeSessionRequest::fromArray([
        'hostId' => 'host-1',
        'handoffId' => 'handoff-1',
        'authentication' => $validated->toArray(),
        'config' => ['sessionId' => 'session-1'],
    ]);
    $cancelled = HostAuthenticationCancelledNotification::fromArray([
        'hostId' => 'host-1',
        'requestId' => 'request-1',
    ]);
    $released = HostSessionReleasedNotification::fromArray([
        'hostId' => 'host-1',
        'handoffId' => 'handoff-1',
    ]);
    $policy = new HostSetSessionCreationPolicyRequest('host-1', false);

    expect($authentication->toArray())->toBe([
        'mode' => 'custom',
        'requireIdentity' => 'tenant-1',
    ])->and($validated->toArray())->toBe([
        'mode' => 'custom',
        'identity' => 'tenant-1',
    ]);

    expect($request->toArray())->toBe([
        'hostId' => 'host-1',
        'requestId' => 'request-1',
        'token' => 'admission-token',
    ])->and($materialize->authentication?->identity)->toBe('tenant-1')
        ->and($cancelled->toArray())->toBe(['hostId' => 'host-1', 'requestId' => 'request-1'])
        ->and($released->toArray())->toBe(['hostId' => 'host-1', 'handoffId' => 'handoff-1'])
        ->and($policy->toArray())->toBe(['hostId' => 'host-1', 'allowCreatingNewSessions' => false]);
});

it('rejects an unsupported AHP host authentication mode', function () {
    expect(fn () => HostAuthentication::fromArray(['mode' => 'password']))
        ->toThrow(ValueError::class);
});
