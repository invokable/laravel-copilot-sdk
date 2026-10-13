<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentMode;
use Revolution\Copilot\Types\Rpc\QueueDuplicateAtRequest;
use Revolution\Copilot\Types\Rpc\QueueDuplicateAtResult;
use Revolution\Copilot\Types\Rpc\QueueInsertAtRequest;
use Revolution\Copilot\Types\Rpc\QueueInsertAtResult;
use Revolution\Copilot\Types\Rpc\QueueInsertMessage;
use Revolution\Copilot\Types\Rpc\QueueMoveItemRequest;
use Revolution\Copilot\Types\Rpc\QueueMoveItemResult;
use Revolution\Copilot\Types\Rpc\QueueRemoveAtRequest;
use Revolution\Copilot\Types\Rpc\QueueRemoveAtResult;
use Revolution\Copilot\Types\Rpc\QueueSendNowRequest;
use Revolution\Copilot\Types\Rpc\QueueSendNowResult;
use Revolution\Copilot\Types\Rpc\QueueSetDrainPausedRequest;
use Revolution\Copilot\Types\Rpc\QueueUpdateTextRequest;
use Revolution\Copilot\Types\Rpc\QueueUpdateTextResult;

it('round trips the queue insert message fields', function () {
    $message = QueueInsertMessage::fromArray([
        'prompt' => 'Write tests',
        'displayPrompt' => 'Test the feature',
        'attachments' => [['type' => 'file', 'path' => '/tmp/notes.txt']],
        'agentMode' => 'plan',
        'source' => 'sdk',
        'billable' => false,
        'requiredTool' => 'test-runner',
        'requestHeaders' => ['X-Request-Id' => 'request-123'],
        'mode' => 'interactive',
        'prepend' => false,
        'wait' => true,
    ]);

    expect($message->agentMode)->toBe(AgentMode::PLAN)
        ->and($message->toArray())->toBe([
            'prompt' => 'Write tests',
            'displayPrompt' => 'Test the feature',
            'attachments' => [['type' => 'file', 'path' => '/tmp/notes.txt']],
            'agentMode' => 'plan',
            'source' => 'sdk',
            'billable' => false,
            'requiredTool' => 'test-runner',
            'requestHeaders' => ['X-Request-Id' => 'request-123'],
            'mode' => 'interactive',
            'prepend' => false,
            'wait' => true,
        ]);
});

it('omits unset optional queue insert and update fields', function () {
    $message = new QueueInsertMessage(prompt: 'A prompt');
    $request = QueueUpdateTextRequest::fromArray([
        'id' => 'queue-1',
        'prompt' => 'Updated prompt',
    ]);

    expect($message->toArray())->toBe(['prompt' => 'A prompt'])
        ->and($request->toArray())->toBe([
            'id' => 'queue-1',
            'prompt' => 'Updated prompt',
        ]);
});

it('round trips queue editing requests', function () {
    $cases = [
        [QueueInsertAtRequest::class, [
            'position' => 2,
            'message' => ['prompt' => 'Queued prompt', 'agentMode' => 'plan'],
        ]],
        [QueueMoveItemRequest::class, ['id' => 'queue-1', 'toPosition' => 0]],
        [QueueRemoveAtRequest::class, ['id' => 'queue-1']],
        [QueueDuplicateAtRequest::class, ['id' => 'queue-1']],
        [QueueSendNowRequest::class, ['id' => 'queue-1']],
        [QueueSetDrainPausedRequest::class, ['paused' => false]],
        [QueueUpdateTextRequest::class, [
            'id' => 'queue-1',
            'prompt' => 'Updated prompt',
            'displayPrompt' => 'Updated label',
        ]],
    ];

    foreach ($cases as [$type, $payload]) {
        expect($type::fromArray($payload)->toArray())->toBe($payload);
    }
});

it('round trips queue editing results', function () {
    $cases = [
        [QueueInsertAtResult::class, ['id' => 'queue-1']],
        [QueueMoveItemResult::class, ['changed' => false]],
        [QueueRemoveAtResult::class, ['removed' => true]],
        [QueueDuplicateAtResult::class, ['id' => 'queue-2']],
        [QueueSendNowResult::class, ['steered' => false]],
        [QueueUpdateTextResult::class, ['updated' => true]],
    ];

    foreach ($cases as [$type, $payload]) {
        expect($type::fromArray($payload)->toArray())->toBe($payload);
    }
});
