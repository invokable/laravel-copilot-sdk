<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ContextHeaviestMessage;

describe('ContextHeaviestMessage', function () {
    it('implements Arrayable', function () {
        expect(new ContextHeaviestMessage('msg-1', 'tool: bash', 'tool', 1200))
            ->toBeInstanceOf(Arrayable::class);
    });

    it('can be created from array', function () {
        $msg = ContextHeaviestMessage::fromArray([
            'id' => 'msg-1',
            'label' => 'tool: bash',
            'role' => 'tool',
            'tokens' => 1200,
        ]);

        expect($msg->id)->toBe('msg-1')
            ->and($msg->label)->toBe('tool: bash')
            ->and($msg->role)->toBe('tool')
            ->and($msg->tokens)->toBe(1200);
    });

    it('defaults missing fields to empty/zero', function () {
        $msg = ContextHeaviestMessage::fromArray([]);

        expect($msg->id)->toBe('')
            ->and($msg->label)->toBe('')
            ->and($msg->role)->toBe('')
            ->and($msg->tokens)->toBe(0);
    });

    it('converts to array', function () {
        $msg = new ContextHeaviestMessage('msg-2', 'skill: tmux', 'user', 500);

        expect($msg->toArray())->toBe([
            'id' => 'msg-2',
            'label' => 'skill: tmux',
            'role' => 'user',
            'tokens' => 500,
        ]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'id' => 'msg-3',
            'label' => 'assistant',
            'role' => 'assistant',
            'tokens' => 800,
        ];

        $msg = ContextHeaviestMessage::fromArray($data);

        expect($msg->toArray())->toBe($data);
    });
});
