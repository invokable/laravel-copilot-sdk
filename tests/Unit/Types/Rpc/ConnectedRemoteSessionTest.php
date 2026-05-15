<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ConnectedRemoteSessionMetadataKind;
use Revolution\Copilot\Enums\RemoteSessionMode;
use Revolution\Copilot\Types\Rpc\ConnectedRemoteSessionMetadata;
use Revolution\Copilot\Types\Rpc\ConnectedRemoteSessionMetadataRepository;
use Revolution\Copilot\Types\Rpc\ConnectRemoteSessionParams;
use Revolution\Copilot\Types\Rpc\RemoteEnableRequest;
use Revolution\Copilot\Types\Rpc\RemoteSessionConnectionResult;

describe('ConnectRemoteSessionParams', function () {
    it('can be created from array', function () {
        $params = ConnectRemoteSessionParams::fromArray(['sessionId' => 'abc-123']);

        expect($params->sessionId)->toBe('abc-123');
    });

    it('converts to array', function () {
        $params = ConnectRemoteSessionParams::fromArray(['sessionId' => 'abc-123']);

        expect($params->toArray())->toHaveKey('sessionId', 'abc-123');
    });
});

describe('ConnectedRemoteSessionMetadataRepository', function () {
    it('can be created from array', function () {
        $repo = ConnectedRemoteSessionMetadataRepository::fromArray([
            'branch' => 'main',
            'name' => 'my-repo',
            'owner' => 'myorg',
        ]);

        expect($repo->branch)->toBe('main')
            ->and($repo->name)->toBe('my-repo')
            ->and($repo->owner)->toBe('myorg');
    });

    it('converts to array', function () {
        $repo = ConnectedRemoteSessionMetadataRepository::fromArray([
            'branch' => 'main',
            'name' => 'my-repo',
            'owner' => 'myorg',
        ]);
        $array = $repo->toArray();

        expect($array)->toHaveKey('branch', 'main')
            ->and($array)->toHaveKey('name', 'my-repo')
            ->and($array)->toHaveKey('owner', 'myorg');
    });
});

describe('ConnectedRemoteSessionMetadata', function () {
    it('can be created with all fields', function () {
        $metadata = ConnectedRemoteSessionMetadata::fromArray([
            'kind' => 'coding-agent',
            'modifiedTime' => '2024-01-01T00:00:00Z',
            'repository' => ['branch' => 'main', 'name' => 'repo', 'owner' => 'org'],
            'sessionId' => 'session-123',
            'startTime' => '2024-01-01T00:00:00Z',
            'name' => 'My Session',
            'pullRequestNumber' => 42,
            'resourceId' => 'res-456',
            'staleAt' => '2024-12-31T00:00:00Z',
            'state' => 'active',
            'summary' => 'Test summary',
        ]);

        expect($metadata->kind)->toBe(ConnectedRemoteSessionMetadataKind::CodingAgent)
            ->and($metadata->sessionId)->toBe('session-123')
            ->and($metadata->name)->toBe('My Session')
            ->and($metadata->pullRequestNumber)->toBe(42)
            ->and($metadata->repository)->toBeInstanceOf(ConnectedRemoteSessionMetadataRepository::class);
    });

    it('can be created with minimal fields', function () {
        $metadata = ConnectedRemoteSessionMetadata::fromArray([
            'kind' => 'remote-session',
            'modifiedTime' => '2024-01-01T00:00:00Z',
            'repository' => ['branch' => 'main', 'name' => 'repo', 'owner' => 'org'],
            'sessionId' => 'session-123',
            'startTime' => '2024-01-01T00:00:00Z',
        ]);

        expect($metadata->kind)->toBe(ConnectedRemoteSessionMetadataKind::RemoteSession)
            ->and($metadata->name)->toBeNull()
            ->and($metadata->pullRequestNumber)->toBeNull();
    });

    it('converts to array', function () {
        $metadata = ConnectedRemoteSessionMetadata::fromArray([
            'kind' => 'coding-agent',
            'modifiedTime' => '2024-01-01T00:00:00Z',
            'repository' => ['branch' => 'main', 'name' => 'repo', 'owner' => 'org'],
            'sessionId' => 'session-123',
            'startTime' => '2024-01-01T00:00:00Z',
        ]);
        $array = $metadata->toArray();

        expect($array)->toHaveKey('kind', 'coding-agent')
            ->and($array)->toHaveKey('sessionId', 'session-123')
            ->and($array)->not->toHaveKey('name');
    });
});

describe('RemoteSessionConnectionResult', function () {
    it('can be created from array', function () {
        $result = RemoteSessionConnectionResult::fromArray([
            'metadata' => [
                'kind' => 'coding-agent',
                'modifiedTime' => '2024-01-01T00:00:00Z',
                'repository' => ['branch' => 'main', 'name' => 'repo', 'owner' => 'org'],
                'sessionId' => 'session-123',
                'startTime' => '2024-01-01T00:00:00Z',
            ],
            'sessionId' => 'session-456',
        ]);

        expect($result->sessionId)->toBe('session-456')
            ->and($result->metadata)->toBeInstanceOf(ConnectedRemoteSessionMetadata::class);
    });

    it('converts to array', function () {
        $result = RemoteSessionConnectionResult::fromArray([
            'metadata' => [
                'kind' => 'coding-agent',
                'modifiedTime' => '2024-01-01T00:00:00Z',
                'repository' => ['branch' => 'main', 'name' => 'repo', 'owner' => 'org'],
                'sessionId' => 'session-123',
                'startTime' => '2024-01-01T00:00:00Z',
            ],
            'sessionId' => 'session-456',
        ]);
        $array = $result->toArray();

        expect($array)->toHaveKey('sessionId', 'session-456')
            ->and($array)->toHaveKey('metadata');
    });
});

describe('RemoteEnableRequest', function () {
    it('can be created with mode', function () {
        $req = RemoteEnableRequest::fromArray(['mode' => 'export']);

        expect($req->mode)->toBe(RemoteSessionMode::Export);
    });

    it('can be created empty', function () {
        $req = RemoteEnableRequest::fromArray([]);

        expect($req->mode)->toBeNull();
    });

    it('converts to array with mode', function () {
        $req = new RemoteEnableRequest(mode: RemoteSessionMode::On);
        $array = $req->toArray();

        expect($array)->toHaveKey('mode', 'on');
    });

    it('converts to array without null mode', function () {
        $req = new RemoteEnableRequest;
        $array = $req->toArray();

        expect($array)->not->toHaveKey('mode');
    });
});
