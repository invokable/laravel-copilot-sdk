<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\QueuePendingItemsKind;
use Revolution\Copilot\Types\Rpc\QueuePendingItems;
use Revolution\Copilot\Types\Rpc\QueuePendingItemsResult;
use Revolution\Copilot\Types\Rpc\QueueRemoveMostRecentResult;

describe('QueuePendingItems', function () {
    it('can be created from array', function () {
        $item = QueuePendingItems::fromArray([
            'displayText' => '/compact',
            'kind' => 'command',
        ]);

        expect($item->displayText)->toBe('/compact')
            ->and($item->kind)->toBe(QueuePendingItemsKind::Command);
    });

    it('defaults kind to message', function () {
        $item = QueuePendingItems::fromArray(['displayText' => 'hello']);

        expect($item->kind)->toBe(QueuePendingItemsKind::Message);
    });

    it('converts to array', function () {
        $item = new QueuePendingItems(
            displayText: 'tell me something',
            kind: QueuePendingItemsKind::Message,
        );

        expect($item->toArray())->toBe([
            'displayText' => 'tell me something',
            'kind' => 'message',
        ]);
    });

    it('implements Arrayable', function () {
        $item = new QueuePendingItems(displayText: 'x', kind: QueuePendingItemsKind::Command);
        expect($item)->toBeInstanceOf(Arrayable::class);
    });
});

describe('QueuePendingItemsResult', function () {
    it('can be created from empty array', function () {
        $result = QueuePendingItemsResult::fromArray([]);

        expect($result->items)->toBe([])
            ->and($result->steeringMessages)->toBe([]);
    });

    it('can be created with items and steering messages', function () {
        $result = QueuePendingItemsResult::fromArray([
            'items' => [
                ['displayText' => '/compact', 'kind' => 'command'],
                ['displayText' => 'hello', 'kind' => 'message'],
            ],
            'steeringMessages' => ['stop that', 'focus on tests'],
        ]);

        expect($result->items)->toHaveCount(2)
            ->and($result->items[0])->toBeInstanceOf(QueuePendingItems::class)
            ->and($result->items[0]->kind)->toBe(QueuePendingItemsKind::Command)
            ->and($result->items[1]->kind)->toBe(QueuePendingItemsKind::Message)
            ->and($result->steeringMessages)->toBe(['stop that', 'focus on tests']);
    });

    it('converts to array', function () {
        $result = new QueuePendingItemsResult(
            items: [new QueuePendingItems(displayText: '/model gpt-4', kind: QueuePendingItemsKind::Command)],
            steeringMessages: [],
        );

        $array = $result->toArray();

        expect($array['items'])->toHaveCount(1)
            ->and($array['items'][0]['kind'])->toBe('command')
            ->and($array['steeringMessages'])->toBe([]);
    });

    it('implements Arrayable', function () {
        expect(new QueuePendingItemsResult)->toBeInstanceOf(Arrayable::class);
    });
});

describe('QueueRemoveMostRecentResult', function () {
    it('can be created from array with removed=true', function () {
        $result = QueueRemoveMostRecentResult::fromArray(['removed' => true]);

        expect($result->removed)->toBeTrue();
    });

    it('can be created from array with removed=false', function () {
        $result = QueueRemoveMostRecentResult::fromArray(['removed' => false]);

        expect($result->removed)->toBeFalse();
    });

    it('defaults to false', function () {
        $result = QueueRemoveMostRecentResult::fromArray([]);

        expect($result->removed)->toBeFalse();
    });

    it('converts to array', function () {
        $result = new QueueRemoveMostRecentResult(removed: true);

        expect($result->toArray())->toBe(['removed' => true]);
    });

    it('implements Arrayable', function () {
        expect(new QueueRemoveMostRecentResult(removed: false))->toBeInstanceOf(Arrayable::class);
    });
});
