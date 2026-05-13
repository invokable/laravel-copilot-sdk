<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CommandsRespondToQueuedCommandRequest;
use Revolution\Copilot\Types\Rpc\CommandsRespondToQueuedCommandResult;
use Revolution\Copilot\Types\Rpc\QueuedCommandHandled;
use Revolution\Copilot\Types\Rpc\QueuedCommandNotHandled;
use Revolution\Copilot\Types\Rpc\QueuedCommandResult;

describe('QueuedCommandResult', function () {
    it('can be created from array with all fields', function () {
        $result = QueuedCommandResult::fromArray([
            'handled' => true,
            'stopProcessingQueue' => true,
        ]);

        expect($result->handled)->toBeTrue()
            ->and($result->stopProcessingQueue)->toBeTrue();
    });

    it('can be created with only required fields', function () {
        $result = QueuedCommandResult::fromArray(['handled' => false]);

        expect($result->handled)->toBeFalse()
            ->and($result->stopProcessingQueue)->toBeNull();
    });

    it('converts to array omitting null values', function () {
        $result = QueuedCommandResult::fromArray(['handled' => true]);

        expect($result->toArray())->toHaveKey('handled', true)
            ->and($result->toArray())->not->toHaveKey('stopProcessingQueue');
    });

    it('includes stopProcessingQueue in toArray when set', function () {
        $result = QueuedCommandResult::fromArray(['handled' => true, 'stopProcessingQueue' => false]);

        expect($result->toArray())->toHaveKey('stopProcessingQueue', false);
    });
});

describe('QueuedCommandHandled', function () {
    it('can be created from array with all fields', function () {
        $result = QueuedCommandHandled::fromArray([
            'handled' => true,
            'stopProcessingQueue' => true,
        ]);

        expect($result->handled)->toBeTrue()
            ->and($result->stopProcessingQueue)->toBeTrue();
    });

    it('can be created with minimal data', function () {
        $result = QueuedCommandHandled::fromArray([]);

        expect($result->handled)->toBeTrue()
            ->and($result->stopProcessingQueue)->toBeNull();
    });

    it('converts to array omitting null values', function () {
        $result = QueuedCommandHandled::fromArray(['handled' => true]);

        expect($result->toArray())->toHaveKey('handled', true)
            ->and($result->toArray())->not->toHaveKey('stopProcessingQueue');
    });
});

describe('QueuedCommandNotHandled', function () {
    it('can be created from array', function () {
        $result = QueuedCommandNotHandled::fromArray(['handled' => false]);

        expect($result->handled)->toBeFalse();
    });

    it('converts to array', function () {
        $result = QueuedCommandNotHandled::fromArray(['handled' => false]);

        expect($result->toArray())->toBe(['handled' => false]);
    });
});

describe('CommandsRespondToQueuedCommandRequest', function () {
    it('can be created from array', function () {
        $request = CommandsRespondToQueuedCommandRequest::fromArray([
            'requestId' => 'req-123',
            'result' => ['handled' => true],
        ]);

        expect($request->requestId)->toBe('req-123')
            ->and($request->result)->toBeInstanceOf(QueuedCommandResult::class)
            ->and($request->result->handled)->toBeTrue();
    });

    it('converts to array', function () {
        $request = CommandsRespondToQueuedCommandRequest::fromArray([
            'requestId' => 'req-456',
            'result' => ['handled' => false],
        ]);

        $array = $request->toArray();

        expect($array['requestId'])->toBe('req-456')
            ->and($array['result'])->toHaveKey('handled', false);
    });
});

describe('CommandsRespondToQueuedCommandResult', function () {
    it('can be created from array', function () {
        $result = CommandsRespondToQueuedCommandResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue();
    });

    it('can be created with false success', function () {
        $result = CommandsRespondToQueuedCommandResult::fromArray(['success' => false]);

        expect($result->success)->toBeFalse();
    });

    it('converts to array', function () {
        $result = CommandsRespondToQueuedCommandResult::fromArray(['success' => true]);

        expect($result->toArray())->toBe(['success' => true]);
    });
});
