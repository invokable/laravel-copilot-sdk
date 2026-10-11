<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\HostCreateSessionRequest;
use Revolution\Copilot\Types\Rpc\HostGitHubEnvironmentOptions;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;
use Revolution\Copilot\Types\Rpc\HostStartRequest;

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
