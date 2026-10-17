<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\DebugCollectLogsEntryKind;
use Revolution\Copilot\Enums\DebugCollectLogsRedaction;
use Revolution\Copilot\Enums\DebugCollectLogsResultKind;
use Revolution\Copilot\Enums\DebugCollectLogsSource;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingDebug;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsCollectedEntry;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsDestination;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsEntry;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsInclude;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsRequest;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsResult;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsSkippedEntry;

describe('PendingDebug', function () {
    it('collects logs with typed params and decodes the typed result', function () {
        $response = [
            'kind' => 'archive',
            'path' => '/tmp/copilot-debug.tgz',
            'entries' => [
                ['bundlePath' => 'events.jsonl', 'source' => 'events', 'sizeBytes' => 128],
            ],
            'skippedEntries' => [
                ['bundlePath' => 'application/app.log', 'reason' => 'not-found'],
            ],
        ];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.debug.collectLogs', [
                'destination' => [
                    'kind' => 'archive',
                    'outputPath' => '/tmp/copilot-debug.tgz',
                    'noOverwrite' => true,
                ],
                'include' => [
                    'events' => false,
                    'previousProcessLogLimit' => 2,
                ],
                'additionalEntries' => [
                    [
                        'kind' => 'file',
                        'path' => '/tmp/app.log',
                        'bundlePath' => 'application/app.log',
                        'redaction' => 'plain-text',
                        'required' => false,
                    ],
                ],
                'sessionId' => 'test-session',
            ])
            ->andReturn($response);

        $result = (new SessionRpc($client, 'test-session'))->debug()->collectLogs(
            new DebugCollectLogsRequest(
                destination: DebugCollectLogsDestination::archive('/tmp/copilot-debug.tgz', noOverwrite: true),
                include: new DebugCollectLogsInclude(events: false, previousProcessLogLimit: 2),
                additionalEntries: [
                    new DebugCollectLogsEntry(
                        kind: DebugCollectLogsEntryKind::File,
                        path: '/tmp/app.log',
                        bundlePath: 'application/app.log',
                        redaction: DebugCollectLogsRedaction::PlainText,
                        required: false,
                    ),
                ],
            ),
        );

        expect($result)->toBeInstanceOf(DebugCollectLogsResult::class)
            ->and($result->kind)->toBe(DebugCollectLogsResultKind::Archive)
            ->and($result->entries[0])->toBeInstanceOf(DebugCollectLogsCollectedEntry::class)
            ->and($result->entries[0]->source)->toBe(DebugCollectLogsSource::Events)
            ->and($result->entries[0]->sizeBytes)->toBe(128)
            ->and($result->skippedEntries[0])->toBeInstanceOf(DebugCollectLogsSkippedEntry::class)
            ->and($result->skippedEntries[0]->path)->toBeNull()
            ->and($result->toArray())->toBe($response);
    });

    it('accepts array params for a directory destination and omits unspecified options', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.debug.collectLogs', [
                'destination' => [
                    'kind' => 'directory',
                    'outputDirectory' => '/tmp/copilot-debug',
                ],
                'include' => ['shellLogs' => true],
                'sessionId' => 'test-session',
            ])
            ->andReturn([
                'kind' => 'directory',
                'path' => '/tmp/copilot-debug',
                'entries' => [],
            ]);

        $pending = new PendingDebug($client, 'test-session');
        $result = $pending->collectLogs([
            'destination' => [
                'kind' => 'directory',
                'outputDirectory' => '/tmp/copilot-debug',
            ],
            'include' => ['shellLogs' => true],
        ]);

        expect($result->kind)->toBe(DebugCollectLogsResultKind::Directory)
            ->and($result->entries)->toBe([]);
    });
});
