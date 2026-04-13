<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ContextWindow;

describe('ContextWindow', function () {
    it('can be created from array with all fields', function () {
        $cw = ContextWindow::fromArray([
            'tokenLimit' => 128000,
            'currentTokens' => 50000,
            'messagesLength' => 42,
            'systemTokens' => 5000,
            'conversationTokens' => 40000,
            'toolDefinitionsTokens' => 5000,
        ]);

        expect($cw->tokenLimit)->toBe(128000)
            ->and($cw->currentTokens)->toBe(50000)
            ->and($cw->messagesLength)->toBe(42)
            ->and($cw->systemTokens)->toBe(5000)
            ->and($cw->conversationTokens)->toBe(40000)
            ->and($cw->toolDefinitionsTokens)->toBe(5000);
    });

    it('can be created from array with required fields only', function () {
        $cw = ContextWindow::fromArray([
            'tokenLimit' => 128000,
            'currentTokens' => 50000,
            'messagesLength' => 10,
        ]);

        expect($cw->tokenLimit)->toBe(128000)
            ->and($cw->currentTokens)->toBe(50000)
            ->and($cw->messagesLength)->toBe(10)
            ->and($cw->systemTokens)->toBeNull()
            ->and($cw->conversationTokens)->toBeNull()
            ->and($cw->toolDefinitionsTokens)->toBeNull();
    });

    it('converts to array with all fields', function () {
        $cw = new ContextWindow(
            tokenLimit: 128000,
            currentTokens: 50000,
            messagesLength: 42,
            systemTokens: 5000,
            conversationTokens: 40000,
            toolDefinitionsTokens: 5000,
        );

        expect($cw->toArray())->toBe([
            'tokenLimit' => 128000,
            'currentTokens' => 50000,
            'messagesLength' => 42,
            'systemTokens' => 5000,
            'conversationTokens' => 40000,
            'toolDefinitionsTokens' => 5000,
        ]);
    });

    it('excludes null optional fields from toArray', function () {
        $cw = new ContextWindow(
            tokenLimit: 128000,
            currentTokens: 50000,
            messagesLength: 10,
        );

        $array = $cw->toArray();

        expect($array)->toHaveCount(3)
            ->and($array)->not->toHaveKey('systemTokens')
            ->and($array)->not->toHaveKey('conversationTokens')
            ->and($array)->not->toHaveKey('toolDefinitionsTokens');
    });

    it('implements Arrayable interface', function () {
        $cw = new ContextWindow(tokenLimit: 1, currentTokens: 1, messagesLength: 1);
        expect($cw)->toBeInstanceOf(Arrayable::class);
    });
});
