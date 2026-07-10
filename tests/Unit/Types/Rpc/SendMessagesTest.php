<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SendMessageItem;
use Revolution\Copilot\Types\Rpc\SendMessagesRequest;
use Revolution\Copilot\Types\Rpc\SendMessagesResult;

describe('SendMessageItem', function () {
    it('can be created with prompt only', function () {
        $item = new SendMessageItem(prompt: 'Hello');

        expect($item->prompt)->toBe('Hello')
            ->and($item->displayPrompt)->toBeNull()
            ->and($item->attachments)->toBeNull()
            ->and($item->requiredTool)->toBeNull();
    });

    it('can be created from array', function () {
        $item = SendMessageItem::fromArray([
            'prompt' => 'Tell me something',
            'displayPrompt' => 'Show this',
            'requiredTool' => 'my_tool',
        ]);

        expect($item->prompt)->toBe('Tell me something')
            ->and($item->displayPrompt)->toBe('Show this')
            ->and($item->requiredTool)->toBe('my_tool');
    });

    it('converts to array excluding nulls', function () {
        $item = new SendMessageItem(prompt: 'Hi');

        expect($item->toArray())->toBe(['prompt' => 'Hi']);
    });
});

describe('SendMessagesRequest', function () {
    it('can be created from array with messages', function () {
        $request = SendMessagesRequest::fromArray([
            'messages' => [
                ['prompt' => 'Hello'],
                ['prompt' => 'World'],
            ],
        ]);

        expect($request->messages)->toHaveCount(2)
            ->and($request->messages[0])->toBeInstanceOf(SendMessageItem::class)
            ->and($request->messages[0]->prompt)->toBe('Hello')
            ->and($request->mode)->toBeNull();
    });

    it('can be created with all options', function () {
        $request = new SendMessagesRequest(
            messages: [new SendMessageItem(prompt: 'Hi')],
            mode: 'immediate',
            prepend: true,
            wait: true,
        );

        expect($request->mode)->toBe('immediate')
            ->and($request->prepend)->toBeTrue()
            ->and($request->wait)->toBeTrue();
    });

    it('converts to array excluding nulls', function () {
        $request = new SendMessagesRequest(
            messages: [new SendMessageItem(prompt: 'Hi')],
        );

        expect($request->toArray())->toBe([
            'messages' => [['prompt' => 'Hi']],
        ]);
    });
});

describe('SendMessagesResult', function () {
    it('can be created from array', function () {
        $result = SendMessagesResult::fromArray([
            'messageIds' => ['id1', 'id2'],
        ]);

        expect($result->messageIds)->toBe(['id1', 'id2']);
    });

    it('handles empty messageIds', function () {
        $result = SendMessagesResult::fromArray(['messageIds' => []]);

        expect($result->messageIds)->toBeEmpty();
    });

    it('converts to array', function () {
        $result = new SendMessagesResult(messageIds: ['abc']);

        expect($result->toArray())->toBe(['messageIds' => ['abc']]);
    });
});
