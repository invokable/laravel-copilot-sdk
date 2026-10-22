<?php

declare(strict_types=1);

use Illuminate\Broadcasting\AnonymousEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Broadcast;
use Revolution\Copilot\Enums\ModelCallProviderType;
use Revolution\Copilot\Enums\ModelCallRequestBodyCompressionFallback;
use Revolution\Copilot\Enums\ModelCallRequestBodyEncoding;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

describe('SessionEvent', function () {
    it('can be created from array', function () {
        $event = SessionEvent::fromArray([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => 'parent-id',
            'type' => 'assistant.message',
            'data' => ['content' => 'Hello World'],
            'ephemeral' => false,
            'agentId' => 'sub-agent-1',
        ]);

        expect($event->id)->toBe('test-id')
            ->and($event->timestamp)->toBe('2024-01-01T00:00:00Z')
            ->and($event->parentId)->toBe('parent-id')
            ->and($event->type)->toBe(SessionEventType::ASSISTANT_MESSAGE)
            ->and($event->data)->toBe(['content' => 'Hello World'])
            ->and($event->ephemeral)->toBeFalse()
            ->and($event->agentId)->toBe('sub-agent-1');
    });

    it('handles missing fields with defaults', function () {
        $event = SessionEvent::fromArray([]);

        expect($event->id)->toBe('')
            ->and($event->timestamp)->toBe('')
            ->and($event->parentId)->toBeNull()
            ->and($event->type)->toBe(SessionEventType::SESSION_INFO)
            ->and($event->data)->toBe([])
            ->and($event->ephemeral)->toBeFalse()
            ->and($event->agentId)->toBeNull();
    });

    it('preserves instruction update events and their payload', function () {
        $data = [
            'content' => 'Follow the updated project instructions.',
            'authority' => 'runtime',
            'boundary' => 'nextModelRequest',
            'fallback' => 'reject',
            'lifetime' => 'persistent',
        ];

        $event = SessionEvent::fromArray([
            'type' => 'instruction.update',
            'data' => $data,
            'ephemeral' => false,
        ]);

        expect($event->type)->toBe(SessionEventType::INSTRUCTION_UPDATE)
            ->and($event->data)->toBe($data)
            ->and($event->ephemeral)->toBeFalse();
    });

    it('can check if is assistant message', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => ['content' => 'Test'],
        ]);

        expect($event->isAssistantMessage())->toBeTrue()
            ->and($event->isIdle())->toBeFalse()
            ->and($event->failed())->toBeFalse();
    });

    it('can check if is idle', function () {
        $event = SessionEvent::fromArray([
            'type' => 'session.idle',
        ]);

        expect($event->isIdle())->toBeTrue()
            ->and($event->isAssistantMessage())->toBeFalse()
            ->and($event->failed())->toBeFalse();
    });

    it('can check if is error', function () {
        $event = SessionEvent::fromArray([
            'type' => 'session.error',
            'data' => ['message' => 'Something went wrong'],
        ]);

        expect($event->failed())->toBeTrue()
            ->and($event->isAssistantMessage())->toBeFalse()
            ->and($event->isIdle())->toBeFalse();
    });

    it('can get content from assistant message', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => ['content' => 'Hello World'],
        ]);

        expect($event->content())->toBe('Hello World');
    });

    it('returns null for content when not present', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.message',
            'data' => [],
        ]);

        expect($event->content())->toBeNull();
    });

    it('can get error message', function () {
        $event = SessionEvent::fromArray([
            'type' => 'session.error',
            'data' => ['message' => 'Error occurred'],
        ]);

        expect($event->errorMessage())->toBe('Error occurred');
    });

    it('can convert to array', function () {
        $event = SessionEvent::fromArray([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => null,
            'type' => 'assistant.message',
            'data' => ['content' => 'Test'],
            'ephemeral' => true,
        ]);

        $array = $event->toArray();

        expect($array)->toBe([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'parentId' => null,
            'type' => 'assistant.message',
            'data' => ['content' => 'Test'],
            'ephemeral' => true,
            'agentId' => null,
        ]);
    });

    it('preserves optional compaction and fusion metadata without normalizing it', function () {
        $startData = [
            'compactionAttemptId' => 'attempt-1',
            'model' => 'gpt-4.1',
            'switchTargetModel' => 'gpt-5',
            'targetPromptTokenLimit' => 16384,
        ];
        $start = SessionEvent::fromArray([
            'type' => 'session.compaction_start',
            'data' => $startData,
        ]);

        $completeData = [
            'compactionAttemptId' => 'attempt-1',
            'failureReason' => 'future_failure_reason',
            'initialModel' => 'gpt-4.1',
            'model' => 'gpt-5',
            'outcome' => 'future_outcome',
            'outcomeReason' => 'future_outcome_reason',
            'success' => false,
            'switchTargetModel' => 'gpt-5',
            'targetPromptTokenLimit' => 16384,
            'tokenAccountingBasis' => 'local_full_context_estimate',
        ];
        $complete = SessionEvent::fromArray([
            'type' => 'session.compaction_complete',
            'data' => $completeData,
        ]);

        $withoutOptionalMetadata = SessionEvent::fromArray([
            'type' => 'session.compaction_complete',
            'data' => ['success' => true],
        ]);

        $fusionData = ['selectedRuleId' => 'server-rule-1'];
        $fusion = SessionEvent::fromArray([
            'type' => 'session.fusion_resolved',
            'data' => $fusionData,
        ]);

        expect($start->toArray()['data'])->toBe($startData)
            ->and($complete->toArray()['data'])->toBe($completeData)
            ->and($fusion->toArray()['data'])->toBe($fusionData);

        expect($withoutOptionalMetadata->data)
            ->not->toHaveKey('outcome')
            ->not->toHaveKey('compactionAttemptId');
    });

    it('preserves separate tool usage and runtime deduplication metadata', function () {
        $conversationUsage = [
            'inputTokens' => 40,
            'outputTokens' => 12,
        ];
        $assistantUsage = SessionEvent::fromArray([
            'type' => 'assistant.usage',
            'data' => $conversationUsage,
        ]);

        $toolUsage = [
            'model' => 'gpt-5-mini',
            'requestId' => 'runtime-operation-1',
            'inputTokens' => 14,
            'inputTokensDetails' => [
                'imageTokens' => 5,
                'textTokens' => 9,
            ],
        ];
        $toolExecution = SessionEvent::fromArray([
            'type' => 'tool.execution_complete',
            'data' => [
                'success' => false,
                'toolCallId' => 'tool-call-1',
                'toolUsage' => $toolUsage,
            ],
        ]);

        $toolUsageRequestIds = ['runtime-operation-1', 'runtime-operation-2'];
        foreach (['session.usage_checkpoint', 'session.shutdown'] as $type) {
            $event = SessionEvent::fromArray([
                'type' => $type,
                'data' => ['toolUsageRequestIds' => $toolUsageRequestIds],
            ]);

            expect($event->toArray()['data'])
                ->toBe(['toolUsageRequestIds' => $toolUsageRequestIds]);
        }

        expect($assistantUsage->data)->toBe($conversationUsage)
            ->and($assistantUsage->data)->not->toHaveKey('toolUsage');
        expect($toolExecution->data['toolUsage'])->toBe($toolUsage)
            ->and($toolExecution->data['toolUsage'])->not->toHaveKey('outputTokens')
            ->not->toHaveKey('totalTokens');
    });

    it('provides typed access to model-call telemetry metadata', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.usage',
            'data' => [
                'providerType' => 'azure',
                'requestBodyCompressionFallback' => 'retried',
                'requestBodyCompressionFallbackFrom' => 'zstd',
                'requestBodyCompressionRejectedStatus' => 415,
            ],
        ]);

        expect($event->providerType())->toBe(ModelCallProviderType::AZURE)
            ->and($event->requestBodyCompressionFallback())->toBe(ModelCallRequestBodyCompressionFallback::RETRIED)
            ->and($event->requestBodyCompressionFallbackFrom())->toBe(ModelCallRequestBodyEncoding::ZSTD)
            ->and($event->requestBodyCompressionRejectedStatus())->toBe(415)
            ->and($event->toArray()['data'])->toBe([
                'providerType' => 'azure',
                'requestBodyCompressionFallback' => 'retried',
                'requestBodyCompressionFallbackFrom' => 'zstd',
                'requestBodyCompressionRejectedStatus' => 415,
            ]);
    });

    it('returns null for absent or unsupported model-call telemetry metadata', function () {
        $event = SessionEvent::fromArray([
            'type' => 'model.call_failure',
            'data' => [
                'providerType' => 'future-provider-type',
                'requestBodyCompressionFallback' => 1,
                'requestBodyCompressionFallbackFrom' => 'br',
                'requestBodyCompressionRejectedStatus' => '415',
            ],
        ]);

        expect($event->providerType())->toBeNull()
            ->and($event->requestBodyCompressionFallback())->toBeNull()
            ->and($event->requestBodyCompressionFallbackFrom())->toBeNull()
            ->and($event->requestBodyCompressionRejectedStatus())->toBeNull()
            ->and(SessionEvent::fromArray(['type' => 'assistant.usage'])->providerType())->toBeNull();
    });

    it('provides typed access to model request-shaping counts and preserves failure fingerprints', function () {
        $usageData = [
            'customToolCallCount' => 2,
            'orphanToolCallCount' => 1,
            'unmatchedToolResultCount' => 3,
        ];
        $usage = SessionEvent::fromArray([
            'type' => 'assistant.usage',
            'data' => $usageData,
        ]);

        $fingerprint = [
            'customToolCallCount' => 2,
            'imagePartCount' => 1,
            'messageCount' => 4,
            'namelessToolCallCount' => 0,
            'orphanToolCallCount' => 1,
            'toolCallCount' => 2,
            'toolResultMessageCount' => 1,
            'unmatchedToolResultCount' => 3,
        ];
        $failure = SessionEvent::fromArray([
            'type' => 'model.call_failure',
            'data' => ['requestFingerprint' => $fingerprint],
        ]);

        expect($usage->customToolCallCount())->toBe(2)
            ->and($usage->orphanToolCallCount())->toBe(1)
            ->and($usage->unmatchedToolResultCount())->toBe(3)
            ->and($usage->toArray()['data'])->toBe($usageData)
            ->and($failure->toArray()['data']['requestFingerprint'])->toBe($fingerprint);
    });

    it('returns null for absent or non-integer model request-shaping counts', function () {
        $event = SessionEvent::fromArray([
            'type' => 'assistant.usage',
            'data' => [
                'customToolCallCount' => '2',
                'orphanToolCallCount' => 1.5,
            ],
        ]);

        expect($event->customToolCallCount())->toBeNull()
            ->and($event->orphanToolCallCount())->toBeNull()
            ->and($event->unmatchedToolResultCount())->toBeNull();
    });
});

describe('SessionEvent broadcast', function () {
    it('can broadcast to a channel', function () {
        $anonymous = Mockery::mock(AnonymousEvent::class);
        $anonymous->shouldReceive('as')
            ->once()
            ->with('assistant.message')
            ->andReturnSelf();
        $anonymous->shouldReceive('with')
            ->once()
            ->andReturnSelf();
        $anonymous->shouldReceive('send')
            ->once();

        Broadcast::shouldReceive('on')
            ->once()
            ->andReturn($anonymous);

        $event = SessionEvent::fromArray([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'type' => 'assistant.message',
            'data' => ['content' => 'Hello'],
        ]);

        $event->broadcast(new Channel('test-channel'));
    });

    it('can broadcast now to a channel', function () {
        $anonymous = Mockery::mock(AnonymousEvent::class);
        $anonymous->shouldReceive('as')
            ->once()
            ->with('assistant.message_delta')
            ->andReturnSelf();
        $anonymous->shouldReceive('with')
            ->once()
            ->andReturnSelf();
        $anonymous->shouldReceive('sendNow')
            ->once();

        Broadcast::shouldReceive('on')
            ->once()
            ->andReturn($anonymous);

        $event = SessionEvent::fromArray([
            'id' => 'test-id',
            'timestamp' => '2024-01-01T00:00:00Z',
            'type' => 'assistant.message_delta',
            'data' => ['deltaContent' => 'Hello'],
        ]);

        $event->broadcastNow(new Channel('test-channel'));
    });
});

describe('SessionEventType', function () {
    it('has all expected event types', function () {
        expect(SessionEventType::SESSION_START->value)->toBe('session.start')
            ->and(SessionEventType::SESSION_IDLE->value)->toBe('session.idle')
            ->and(SessionEventType::SESSION_ERROR->value)->toBe('session.error')
            ->and(SessionEventType::ASSISTANT_MESSAGE->value)->toBe('assistant.message')
            ->and(SessionEventType::USER_MESSAGE->value)->toBe('user.message')
            ->and(SessionEventType::TOOL_EXECUTION_START->value)->toBe('tool.execution_start')
            ->and(SessionEventType::TOOL_EXECUTION_COMPLETE->value)->toBe('tool.execution_complete');
    });

    it('can be created from string value', function () {
        $type = SessionEventType::tryFrom('assistant.message');

        expect($type)->toBe(SessionEventType::ASSISTANT_MESSAGE);
    });

    it('returns null for invalid value', function () {
        $type = SessionEventType::tryFrom('invalid.type');

        expect($type)->toBeNull();
    });

    it('has new event types from latest session-events schema', function () {
        expect(SessionEventType::SESSION_TITLE_CHANGED->value)->toBe('session.title_changed')
            ->and(SessionEventType::SESSION_WARNING->value)->toBe('session.warning')
            ->and(SessionEventType::SESSION_MODE_CHANGED->value)->toBe('session.mode_changed')
            ->and(SessionEventType::SESSION_PLAN_CHANGED->value)->toBe('session.plan_changed')
            ->and(SessionEventType::SESSION_WORKSPACE_FILE_CHANGED->value)->toBe('session.workspace_file_changed')
            ->and(SessionEventType::SESSION_TASK_COMPLETE->value)->toBe('session.task_complete')
            ->and(SessionEventType::ASSISTANT_STREAMING_DELTA->value)->toBe('assistant.streaming_delta')
            ->and(SessionEventType::SESSION_INDEXED_SEARCH->value)->toBe('session.indexed_search')
            ->and(SessionEventType::SESSION_MODEL_DESELECTED->value)->toBe('session.model_deselected')
            ->and(SessionEventType::SESSION_PERMISSION_RECOVERY->value)->toBe('session.permission_recovery');
    });

    it('has quota observation and managed plugin progress cases', function () {
        expect(SessionEventType::SESSION_QUOTA_OBSERVATION->value)->toBe('session.quota_observation')
            ->and(SessionEventType::SESSION_MANAGED_PLUGIN_PROGRESS->value)->toBe('session.managed_plugin_progress');
    });

    it('has system.notification case', function () {
        expect(SessionEventType::SYSTEM_NOTIFICATION->value)->toBe('system.notification');
    });

    it('can create system.notification from value', function () {
        expect(SessionEventType::from('system.notification'))
            ->toBe(SessionEventType::SYSTEM_NOTIFICATION);
    });
});
