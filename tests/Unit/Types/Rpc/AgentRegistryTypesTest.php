<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AgentRegistryEnsureDirRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryEnsureDirResult;
use Revolution\Copilot\Types\Rpc\AgentRegistryError;
use Revolution\Copilot\Types\Rpc\AgentRegistryListEntriesRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryListEntriesResult;
use Revolution\Copilot\Types\Rpc\AgentRegistryLiveTargetEntry;
use Revolution\Copilot\Types\Rpc\AgentRegistryRemoveEntryRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryRemoveEntryResult;

describe('agent registry RPC types', function () {
    it('round-trips registry requests and omits optional values that are absent', function () {
        $ensureRequest = AgentRegistryEnsureDirRequest::fromArray(['homeDir' => '/home/alex']);
        $listRequest = AgentRegistryListEntriesRequest::fromArray([
            'homeDir' => '/home/alex',
            'excludePid' => 42,
            'pidAliveOverride' => false,
        ]);
        $removeRequest = AgentRegistryRemoveEntryRequest::fromArray([
            'homeDir' => '/home/alex',
            'pid' => 42,
        ]);

        expect($ensureRequest->toArray())->toBe(['homeDir' => '/home/alex'])
            ->and($listRequest->toArray())->toBe([
                'homeDir' => '/home/alex',
                'excludePid' => 42,
                'pidAliveOverride' => false,
            ])
            ->and($removeRequest->toArray())->toBe([
                'homeDir' => '/home/alex',
                'pid' => 42,
            ]);
    });

    it('round-trips registry results and excludes the internal connection token', function () {
        $entry = [
            'schemaVersion' => 2,
            'kind' => 'managed-server',
            'pid' => 42,
            'host' => '127.0.0.1',
            'port' => 43210,
            'startedAt' => '2026-10-31T12:00:00Z',
            'copilotVersion' => '1.0.106',
            'lastSeenMs' => 1000,
            'sessionId' => 'session-1',
            'token' => 'must-not-be-exposed',
        ];
        $error = ['code' => 'EACCES', 'message' => 'Permission denied'];

        $ensureResult = AgentRegistryEnsureDirResult::fromArray([
            'path' => '/home/alex/.copilot/agents',
            'error' => $error,
        ]);
        $listResult = AgentRegistryListEntriesResult::fromArray([
            'entries' => [$entry],
        ]);
        $removeResult = AgentRegistryRemoveEntryResult::fromArray(['error' => $error]);

        expect($ensureResult->toArray())->toBe([
            'path' => '/home/alex/.copilot/agents',
            'error' => $error,
        ])
            ->and($ensureResult->error)->toBeInstanceOf(AgentRegistryError::class)
            ->and($listResult->entries[0])->toBeInstanceOf(AgentRegistryLiveTargetEntry::class)
            ->and($listResult->toArray())->toBe([
                'entries' => [[
                    'schemaVersion' => 2,
                    'kind' => 'managed-server',
                    'pid' => 42,
                    'host' => '127.0.0.1',
                    'port' => 43210,
                    'sessionId' => 'session-1',
                    'startedAt' => '2026-10-31T12:00:00Z',
                    'copilotVersion' => '1.0.106',
                    'lastSeenMs' => 1000,
                ]],
            ])
            ->and($removeResult->error?->code)->toBe('EACCES');
    });
});
