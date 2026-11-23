<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\HistoryImportAttachmentType;
use Revolution\Copilot\Types\Rpc\HistoryImportAssistantMessage;
use Revolution\Copilot\Types\Rpc\HistoryImportAttachment;
use Revolution\Copilot\Types\Rpc\HistoryImportConversationEvent;
use Revolution\Copilot\Types\Rpc\HistoryImportToolCompletion;
use Revolution\Copilot\Types\Rpc\HistoryImportToolError;
use Revolution\Copilot\Types\Rpc\HistoryImportToolInvocation;
use Revolution\Copilot\Types\Rpc\HistoryImportToolOutput;
use Revolution\Copilot\Types\Rpc\HistoryImportToolRequest;
use Revolution\Copilot\Types\Rpc\HistoryImportUserMessage;
use Revolution\Copilot\Types\Rpc\SessionHistoryImportConversationRequest;

describe('History import types', function () {
    it('round trips every event variant and nested payload', function () {
        $events = [
            new HistoryImportConversationEvent('user.message', new HistoryImportUserMessage('Hello', [
                new HistoryImportAttachment(HistoryImportAttachmentType::Blob, 'text/plain', 'note.txt', 'aGVsbG8='),
            ])),
            new HistoryImportConversationEvent('assistant.message', new HistoryImportAssistantMessage(
                'msg-1', 'Checking', 'model-1', 'turn-1', [
                    new HistoryImportToolRequest('lookup', 'call-1', ['query' => 'hello']),
                ],
            )),
            new HistoryImportConversationEvent('tool.execution_start', new HistoryImportToolInvocation(
                'call-1', 'lookup', ['query' => 'hello'], 'turn-1',
            )),
            new HistoryImportConversationEvent('tool.execution_complete', new HistoryImportToolCompletion(
                'call-1', true, 'turn-1', new HistoryImportToolOutput('Found'),
            )),
            new HistoryImportConversationEvent('tool.execution_complete', new HistoryImportToolCompletion(
                'call-2', false, error: new HistoryImportToolError('Failed', 'ERR'),
            )),
        ];
        $wire = ['events' => [
            ['type' => 'user.message', 'data' => ['content' => 'Hello', 'attachments' => [
                ['type' => 'blob', 'mimeType' => 'text/plain', 'displayName' => 'note.txt', 'data' => 'aGVsbG8='],
            ]]],
            ['type' => 'assistant.message', 'data' => [
                'messageId' => 'msg-1', 'content' => 'Checking', 'model' => 'model-1', 'turnId' => 'turn-1',
                'toolRequests' => [['name' => 'lookup', 'toolCallId' => 'call-1', 'arguments' => ['query' => 'hello']]],
            ]],
            ['type' => 'tool.execution_start', 'data' => [
                'toolCallId' => 'call-1', 'toolName' => 'lookup', 'arguments' => ['query' => 'hello'], 'turnId' => 'turn-1',
            ]],
            ['type' => 'tool.execution_complete', 'data' => [
                'toolCallId' => 'call-1', 'success' => true, 'turnId' => 'turn-1', 'result' => ['content' => 'Found'],
            ]],
            ['type' => 'tool.execution_complete', 'data' => [
                'toolCallId' => 'call-2', 'success' => false, 'error' => ['message' => 'Failed', 'code' => 'ERR'],
            ]],
        ]];

        $request = SessionHistoryImportConversationRequest::fromArray($wire);
        expect((new SessionHistoryImportConversationRequest($events))->toArray())->toBe($wire)
            ->and($request->toArray())->toBe($wire)
            ->and($request->events[0]->data)->toBeInstanceOf(HistoryImportUserMessage::class)
            ->and($request->events[0]->data->attachments[0]->type)->toBe(HistoryImportAttachmentType::Blob)
            ->and($request->events[1]->data)->toBeInstanceOf(HistoryImportAssistantMessage::class)
            ->and($request->events[2]->data)->toBeInstanceOf(HistoryImportToolInvocation::class)
            ->and($request->events[3]->data)->toBeInstanceOf(HistoryImportToolCompletion::class);
    });

    it('preserves empty collections and omits absent optional fields', function () {
        expect((new SessionHistoryImportConversationRequest([]))->toArray())->toBe(['events' => []])
            ->and(HistoryImportUserMessage::fromArray(['content' => '', 'attachments' => []])->toArray())
            ->toBe(['content' => '', 'attachments' => []])
            ->and(HistoryImportAssistantMessage::fromArray(['messageId' => 'm', 'content' => '', 'toolRequests' => []])->toArray())
            ->toBe(['messageId' => 'm', 'content' => '', 'toolRequests' => []])
            ->and((new HistoryImportToolCompletion('call', false))->toArray())->toBe(['toolCallId' => 'call', 'success' => false])
            ->and((new HistoryImportToolError('failed'))->toArray())->toBe(['message' => 'failed']);
    });

    it('preserves every JSON argument value and distinguishes null from omission', function (mixed $value) {
        $request = ['name' => 'tool', 'toolCallId' => 'call', 'arguments' => $value];
        $invocation = ['toolCallId' => 'call', 'toolName' => 'tool', 'arguments' => $value];

        expect(HistoryImportToolRequest::fromArray($request)->toArray())->toBe($request)
            ->and(HistoryImportToolInvocation::fromArray($invocation)->toArray())->toBe($invocation)
            ->and((new HistoryImportToolRequest('tool', 'call', $value))->toArray())->toBe($request)
            ->and((new HistoryImportToolInvocation('call', 'tool', $value))->toArray())->toBe($invocation)
            ->and((new HistoryImportToolRequest('tool', 'call'))->toArray())->toBe(['name' => 'tool', 'toolCallId' => 'call'])
            ->and((new HistoryImportToolInvocation('call', 'tool'))->toArray())->toBe(['toolCallId' => 'call', 'toolName' => 'tool']);
    })->with([[null], [false], [0], [''], [[]], [['nested' => [1, null]]], [(object) []]]);

    it('rejects event types and typed payloads that do not match', function () {
        expect(fn () => HistoryImportConversationEvent::fromArray(['type' => 'unknown', 'data' => []]))
            ->toThrow(InvalidArgumentException::class)
            ->and(fn () => (new HistoryImportConversationEvent('user.message', new HistoryImportToolOutput('x')))->toArray())
            ->toThrow(TypeError::class)
            ->and(fn () => (new HistoryImportConversationEvent('user.message', new HistoryImportToolInvocation('call', 'tool')))->toArray())
            ->toThrow(InvalidArgumentException::class);
    });

    it('preserves future attachment type strings', function () {
        $data = ['type' => 'future', 'mimeType' => 'text/plain', 'displayName' => 'file', 'data' => ''];
        expect(HistoryImportAttachment::fromArray($data)->toArray())->toBe($data);
    });
});
