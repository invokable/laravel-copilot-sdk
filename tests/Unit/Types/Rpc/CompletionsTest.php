<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\CompletionsGetTriggerCharactersResult;
use Revolution\Copilot\Types\Rpc\CompletionsRequestRequest;
use Revolution\Copilot\Types\Rpc\CompletionsRequestResult;
use Revolution\Copilot\Types\Rpc\SessionCompletionItem;

describe('CompletionsGetTriggerCharactersResult', function () {
    it('can be created from array', function () {
        $result = CompletionsGetTriggerCharactersResult::fromArray(['triggerCharacters' => ['@', '#']]);

        expect($result->triggerCharacters)->toBe(['@', '#']);
    });

    it('converts to array', function () {
        $result = new CompletionsGetTriggerCharactersResult(triggerCharacters: ['@']);

        expect($result->toArray())->toBe(['triggerCharacters' => ['@']]);
    });

    it('handles empty trigger characters', function () {
        $result = CompletionsGetTriggerCharactersResult::fromArray(['triggerCharacters' => []]);

        expect($result->triggerCharacters)->toBe([]);
    });

    it('implements Arrayable', function () {
        expect(new CompletionsGetTriggerCharactersResult(triggerCharacters: []))->toBeInstanceOf(Arrayable::class);
    });
});

describe('CompletionsRequestRequest', function () {
    it('can be created from array', function () {
        $request = CompletionsRequestRequest::fromArray(['text' => 'hello @', 'offset' => 7]);

        expect($request->text)->toBe('hello @')
            ->and($request->offset)->toBe(7);
    });

    it('converts to array', function () {
        $request = new CompletionsRequestRequest(text: 'test', offset: 4);

        expect($request->toArray())->toBe(['text' => 'test', 'offset' => 4]);
    });

    it('implements Arrayable', function () {
        expect(new CompletionsRequestRequest(text: '', offset: 0))->toBeInstanceOf(Arrayable::class);
    });
});

describe('SessionCompletionItem', function () {
    it('can be created with only required fields', function () {
        $item = new SessionCompletionItem(insertText: '@user');

        expect($item->insertText)->toBe('@user')
            ->and($item->rangeStart)->toBeNull()
            ->and($item->label)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $item = SessionCompletionItem::fromArray([
            'insertText' => '@alice',
            'rangeStart' => 5,
            'rangeEnd' => 6,
            'label' => 'alice',
            'kind' => 'user',
        ]);

        expect($item->insertText)->toBe('@alice')
            ->and($item->rangeStart)->toBe(5)
            ->and($item->rangeEnd)->toBe(6)
            ->and($item->label)->toBe('alice')
            ->and($item->kind)->toBe('user');
    });

    it('converts to array filtering nulls', function () {
        $item = new SessionCompletionItem(insertText: '@bob');

        expect($item->toArray())->toBe(['insertText' => '@bob']);
    });

    it('implements Arrayable', function () {
        expect(new SessionCompletionItem(insertText: ''))->toBeInstanceOf(Arrayable::class);
    });
});

describe('CompletionsRequestResult', function () {
    it('can be created from array with items', function () {
        $result = CompletionsRequestResult::fromArray([
            'items' => [
                ['insertText' => '@alice', 'label' => 'alice'],
                ['insertText' => '@bob'],
            ],
        ]);

        expect($result->items)->toHaveCount(2)
            ->and($result->items[0])->toBeInstanceOf(SessionCompletionItem::class)
            ->and($result->items[0]->insertText)->toBe('@alice')
            ->and($result->items[1]->insertText)->toBe('@bob');
    });

    it('handles empty items', function () {
        $result = CompletionsRequestResult::fromArray(['items' => []]);

        expect($result->items)->toBe([]);
    });

    it('converts to array', function () {
        $item = new SessionCompletionItem(insertText: '@carol');
        $result = new CompletionsRequestResult(items: [$item]);

        expect($result->toArray())->toBe([
            'items' => [['insertText' => '@carol']],
        ]);
    });

    it('implements Arrayable', function () {
        expect(new CompletionsRequestResult(items: []))->toBeInstanceOf(Arrayable::class);
    });
});
