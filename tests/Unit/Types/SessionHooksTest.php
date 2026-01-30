<?php

declare(strict_types=1);

use Revolution\Copilot\Types\SessionHooks;

describe('SessionHooks', function () {
    it('can be created with all hooks', function () {
        $preToolUse = fn () => null;
        $postToolUse = fn () => null;
        $userPromptSubmitted = fn () => null;
        $sessionStart = fn () => null;
        $sessionEnd = fn () => null;
        $errorOccurred = fn () => null;

        $hooks = new SessionHooks(
            onPreToolUse: $preToolUse,
            onPostToolUse: $postToolUse,
            onUserPromptSubmitted: $userPromptSubmitted,
            onSessionStart: $sessionStart,
            onSessionEnd: $sessionEnd,
            onErrorOccurred: $errorOccurred,
        );

        expect($hooks->onPreToolUse)->toBe($preToolUse)
            ->and($hooks->onPostToolUse)->toBe($postToolUse)
            ->and($hooks->onUserPromptSubmitted)->toBe($userPromptSubmitted)
            ->and($hooks->onSessionStart)->toBe($sessionStart)
            ->and($hooks->onSessionEnd)->toBe($sessionEnd)
            ->and($hooks->onErrorOccurred)->toBe($errorOccurred);
    });

    it('can be created with no hooks', function () {
        $hooks = new SessionHooks;

        expect($hooks->onPreToolUse)->toBeNull()
            ->and($hooks->onPostToolUse)->toBeNull()
            ->and($hooks->onUserPromptSubmitted)->toBeNull()
            ->and($hooks->onSessionStart)->toBeNull()
            ->and($hooks->onSessionEnd)->toBeNull()
            ->and($hooks->onErrorOccurred)->toBeNull();
    });

    it('can be created with partial hooks', function () {
        $preToolUse = fn () => 'pre';
        $sessionEnd = fn () => 'end';

        $hooks = new SessionHooks(
            onPreToolUse: $preToolUse,
            onSessionEnd: $sessionEnd,
        );

        expect($hooks->onPreToolUse)->toBe($preToolUse)
            ->and($hooks->onPostToolUse)->toBeNull()
            ->and($hooks->onSessionEnd)->toBe($sessionEnd);
    });

    it('can be created from array', function () {
        $preToolUse = fn () => null;
        $errorOccurred = fn () => null;

        $hooks = SessionHooks::fromArray([
            'onPreToolUse' => $preToolUse,
            'onErrorOccurred' => $errorOccurred,
        ]);

        expect($hooks->onPreToolUse)->toBe($preToolUse)
            ->and($hooks->onPostToolUse)->toBeNull()
            ->and($hooks->onErrorOccurred)->toBe($errorOccurred);
    });

    it('can be created from empty array', function () {
        $hooks = SessionHooks::fromArray([]);

        expect($hooks->onPreToolUse)->toBeNull()
            ->and($hooks->onPostToolUse)->toBeNull()
            ->and($hooks->onUserPromptSubmitted)->toBeNull()
            ->and($hooks->onSessionStart)->toBeNull()
            ->and($hooks->onSessionEnd)->toBeNull()
            ->and($hooks->onErrorOccurred)->toBeNull();
    });

    it('can convert to array with all hooks', function () {
        $preToolUse = fn () => 'pre';
        $postToolUse = fn () => 'post';

        $hooks = new SessionHooks(
            onPreToolUse: $preToolUse,
            onPostToolUse: $postToolUse,
        );

        $array = $hooks->toArray();

        expect($array)->toHaveKey('onPreToolUse')
            ->and($array)->toHaveKey('onPostToolUse')
            ->and($array['onPreToolUse'])->toBe($preToolUse)
            ->and($array['onPostToolUse'])->toBe($postToolUse);
    });

    it('filters null values in toArray', function () {
        $hooks = new SessionHooks;

        expect($hooks->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $hooks = new SessionHooks;

        expect($hooks)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
