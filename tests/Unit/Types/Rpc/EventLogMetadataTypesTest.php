<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EventsAgentScope;
use Revolution\Copilot\Enums\EventsCursorStatus;
use Revolution\Copilot\Enums\HostType;
use Revolution\Copilot\Types\Rpc\EventLogReadRequest;
use Revolution\Copilot\Types\Rpc\EventLogReleaseInterestResult;
use Revolution\Copilot\Types\Rpc\EventLogTailResult;
use Revolution\Copilot\Types\Rpc\EventsReadResult;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoRequest;
use Revolution\Copilot\Types\Rpc\MetadataContextInfoResult;
use Revolution\Copilot\Types\Rpc\MetadataIsProcessingResult;
use Revolution\Copilot\Types\Rpc\MetadataRecomputeContextTokensRequest;
use Revolution\Copilot\Types\Rpc\MetadataRecomputeContextTokensResult;
use Revolution\Copilot\Types\Rpc\MetadataRecordContextChangeRequest;
use Revolution\Copilot\Types\Rpc\MetadataRecordContextChangeResult;
use Revolution\Copilot\Types\Rpc\MetadataSetWorkingDirectoryRequest;
use Revolution\Copilot\Types\Rpc\MetadataSetWorkingDirectoryResult;
use Revolution\Copilot\Types\Rpc\RegisterEventInterestParams;
use Revolution\Copilot\Types\Rpc\RegisterEventInterestResult;
use Revolution\Copilot\Types\Rpc\ReleaseEventInterestParams;
use Revolution\Copilot\Types\Rpc\SessionContextInfo;
use Revolution\Copilot\Types\Rpc\SessionMetadataSnapshot;
use Revolution\Copilot\Types\Rpc\SessionWorkingDirectoryContext;

describe('Event log types', function () {
    it('converts EventLogReadRequest to and from array', function () {
        $request = new EventLogReadRequest(
            cursor: 'cursor-1',
            max: 20,
            waitMs: 1000,
            types: ['assistant_message'],
            agentScope: EventsAgentScope::PRIMARY,
        );

        expect($request->toArray())->toBe([
            'cursor' => 'cursor-1',
            'max' => 20,
            'waitMs' => 1000,
            'types' => ['assistant_message'],
            'agentScope' => 'primary',
        ]);

        $fromArray = EventLogReadRequest::fromArray(['agentScope' => 'all']);
        expect($fromArray->agentScope)->toBe(EventsAgentScope::ALL);
    });

    it('converts EventsReadResult to and from array', function () {
        $result = EventsReadResult::fromArray([
            'events' => [[
                'id' => 'evt-1',
                'timestamp' => '2026-01-24T10:00:00Z',
                'type' => 'assistant_message',
                'data' => ['content' => 'hello'],
            ]],
            'cursor' => 'cursor-2',
            'hasMore' => true,
            'cursorStatus' => 'expired',
        ]);

        expect($result->events)->toHaveCount(1)
            ->and($result->cursor)->toBe('cursor-2')
            ->and($result->hasMore)->toBeTrue()
            ->and($result->cursorStatus)->toBe(EventsCursorStatus::EXPIRED);

        expect($result->toArray())->toHaveKeys(['events', 'cursor', 'hasMore', 'cursorStatus']);
    });

    it('converts tail and interest types', function () {
        expect(EventLogTailResult::fromArray(['cursor' => 'tail'])->toArray())->toBe(['cursor' => 'tail']);
        expect(RegisterEventInterestParams::fromArray(['eventType' => 'mcp.oauth_required'])->toArray())
            ->toBe(['eventType' => 'mcp.oauth_required']);
        expect(RegisterEventInterestResult::fromArray(['handle' => 'h-1'])->toArray())->toBe(['handle' => 'h-1']);
        expect(ReleaseEventInterestParams::fromArray(['handle' => 'h-1'])->toArray())->toBe(['handle' => 'h-1']);
        expect(EventLogReleaseInterestResult::fromArray(['success' => true])->toArray())->toBe(['success' => true]);
    });
});

describe('Metadata types', function () {
    it('converts SessionMetadataSnapshot to and from array', function () {
        $snapshot = SessionMetadataSnapshot::fromArray([
            'sessionId' => 'session-1',
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:05:00Z',
            'isRemote' => false,
            'alreadyInUse' => true,
            'workspacePath' => null,
            'workingDirectory' => '/workspace',
            'currentMode' => 'interactive',
            'summary' => 'summary',
        ]);

        expect($snapshot->sessionId)->toBe('session-1')
            ->and($snapshot->alreadyInUse)->toBeTrue()
            ->and($snapshot->workingDirectory)->toBe('/workspace')
            ->and($snapshot->toArray())->toHaveKey('currentMode', 'interactive');
    });

    it('converts context info request and result types', function () {
        $request = new MetadataContextInfoRequest(promptTokenLimit: 10000, outputTokenLimit: 4096, selectedModel: 'gpt-5');
        expect($request->toArray())->toBe([
            'promptTokenLimit' => 10000,
            'outputTokenLimit' => 4096,
            'selectedModel' => 'gpt-5',
        ]);

        $result = MetadataContextInfoResult::fromArray([
            'contextInfo' => [
                'bufferTokens' => 100,
                'compactionThreshold' => 5000,
                'conversationTokens' => 3000,
                'limit' => 14096,
                'modelName' => 'gpt-5',
                'promptTokenLimit' => 10000,
                'systemTokens' => 500,
                'toolDefinitionsTokens' => 200,
                'totalTokens' => 3700,
            ],
        ]);

        expect($result->contextInfo)->toBeInstanceOf(SessionContextInfo::class)
            ->and($result->contextInfo->totalTokens)->toBe(3700);
    });

    it('converts processing and recompute token types', function () {
        expect(MetadataIsProcessingResult::fromArray(['processing' => true])->toArray())
            ->toBe(['processing' => true]);
        expect(MetadataRecomputeContextTokensRequest::fromArray(['modelId' => 'gpt-5'])->toArray())
            ->toBe(['modelId' => 'gpt-5']);
        expect(MetadataRecomputeContextTokensResult::fromArray([
            'totalTokens' => 100,
            'messagesTokenCount' => 80,
            'systemTokenCount' => 20,
        ])->toArray())->toBe([
            'totalTokens' => 100,
            'messagesTokenCount' => 80,
            'systemTokenCount' => 20,
        ]);
    });

    it('converts context change and working directory types', function () {
        $context = SessionWorkingDirectoryContext::fromArray([
            'cwd' => '/workspace',
            'gitRoot' => '/workspace',
            'repository' => 'owner/repo',
            'hostType' => 'github',
            'branch' => 'main',
        ]);

        expect($context->hostType)->toBe(HostType::GITHUB);

        $request = MetadataRecordContextChangeRequest::fromArray([
            'context' => $context->toArray(),
        ]);

        expect($request->toArray()['context']['cwd'])->toBe('/workspace');
        expect(MetadataRecordContextChangeResult::fromArray([])->toArray())->toBe([]);
        expect(MetadataSetWorkingDirectoryRequest::fromArray(['workingDirectory' => '/new'])->toArray())
            ->toBe(['workingDirectory' => '/new']);
        expect(MetadataSetWorkingDirectoryResult::fromArray(['workingDirectory' => '/new'])->toArray())
            ->toBe(['workingDirectory' => '/new']);
    });
});
