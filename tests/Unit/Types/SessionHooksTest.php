<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\SessionHooks;

describe('SessionHooks', function () {
    it('can be created with all hooks', function () {
        $preToolUse = fn () => null;
        $postToolUse = fn () => null;
        $postToolUseFailure = fn () => null;
        $userPromptSubmitted = fn () => null;
        $sessionStart = fn () => null;
        $sessionEnd = fn () => null;
        $errorOccurred = fn () => null;
        $preMcpToolCall = fn () => null;
        $agentStop = fn () => null;
        $subagentStart = fn () => null;
        $subagentStop = fn () => null;

        $hooks = new SessionHooks(
            onPreToolUse: $preToolUse,
            onPostToolUse: $postToolUse,
            onPostToolUseFailure: $postToolUseFailure,
            onUserPromptSubmitted: $userPromptSubmitted,
            onSessionStart: $sessionStart,
            onSessionEnd: $sessionEnd,
            onErrorOccurred: $errorOccurred,
            onPreMcpToolCall: $preMcpToolCall,
            onAgentStop: $agentStop,
            onSubagentStart: $subagentStart,
            onSubagentStop: $subagentStop,
        );

        expect($hooks->onPreToolUse)->toBe($preToolUse)
            ->and($hooks->onPostToolUse)->toBe($postToolUse)
            ->and($hooks->onPostToolUseFailure)->toBe($postToolUseFailure)
            ->and($hooks->onUserPromptSubmitted)->toBe($userPromptSubmitted)
            ->and($hooks->onSessionStart)->toBe($sessionStart)
            ->and($hooks->onSessionEnd)->toBe($sessionEnd)
            ->and($hooks->onErrorOccurred)->toBe($errorOccurred)
            ->and($hooks->onPreMcpToolCall)->toBe($preMcpToolCall)
            ->and($hooks->onAgentStop)->toBe($agentStop)
            ->and($hooks->onSubagentStart)->toBe($subagentStart)
            ->and($hooks->onSubagentStop)->toBe($subagentStop);
    });

    it('can be created with no hooks', function () {
        $hooks = new SessionHooks;

        expect($hooks->onPreToolUse)->toBeNull()
            ->and($hooks->onPostToolUse)->toBeNull()
            ->and($hooks->onPostToolUseFailure)->toBeNull()
            ->and($hooks->onUserPromptSubmitted)->toBeNull()
            ->and($hooks->onSessionStart)->toBeNull()
            ->and($hooks->onSessionEnd)->toBeNull()
            ->and($hooks->onErrorOccurred)->toBeNull()
            ->and($hooks->onPreMcpToolCall)->toBeNull()
            ->and($hooks->onAgentStop)->toBeNull()
            ->and($hooks->onSubagentStart)->toBeNull()
            ->and($hooks->onSubagentStop)->toBeNull();
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
            ->and($hooks->onPostToolUseFailure)->toBeNull()
            ->and($hooks->onSessionEnd)->toBe($sessionEnd);
    });

    it('can be created from array', function () {
        $preToolUse = fn () => null;
        $postToolUseFailure = fn () => null;
        $errorOccurred = fn () => null;
        $subagentStart = fn () => null;
        $subagentStop = fn () => null;

        $hooks = SessionHooks::fromArray([
            'onPreToolUse' => $preToolUse,
            'onPostToolUseFailure' => $postToolUseFailure,
            'onErrorOccurred' => $errorOccurred,
            'onSubagentStart' => $subagentStart,
            'onSubagentStop' => $subagentStop,
        ]);

        expect($hooks->onPreToolUse)->toBe($preToolUse)
            ->and($hooks->onPostToolUse)->toBeNull()
            ->and($hooks->onPostToolUseFailure)->toBe($postToolUseFailure)
            ->and($hooks->onErrorOccurred)->toBe($errorOccurred)
            ->and($hooks->onSubagentStart)->toBe($subagentStart)
            ->and($hooks->onSubagentStop)->toBe($subagentStop);
    });

    it('can be created from empty array', function () {
        $hooks = SessionHooks::fromArray([]);

        expect($hooks->onPreToolUse)->toBeNull()
            ->and($hooks->onPostToolUse)->toBeNull()
            ->and($hooks->onPostToolUseFailure)->toBeNull()
            ->and($hooks->onUserPromptSubmitted)->toBeNull()
            ->and($hooks->onSessionStart)->toBeNull()
            ->and($hooks->onSessionEnd)->toBeNull()
            ->and($hooks->onErrorOccurred)->toBeNull()
            ->and($hooks->onPreMcpToolCall)->toBeNull()
            ->and($hooks->onAgentStop)->toBeNull()
            ->and($hooks->onSubagentStart)->toBeNull()
            ->and($hooks->onSubagentStop)->toBeNull();
    });

    it('can convert to array with all hooks', function () {
        $preToolUse = fn () => 'pre';
        $postToolUse = fn () => 'post';
        $postToolUseFailure = fn () => 'failure';

        $hooks = new SessionHooks(
            onPreToolUse: $preToolUse,
            onPostToolUse: $postToolUse,
            onPostToolUseFailure: $postToolUseFailure,
            onSubagentStart: fn () => 'start',
            onSubagentStop: fn () => 'stop',
        );

        $array = $hooks->toArray();

        expect($array)->toHaveKey('onPreToolUse')
            ->and($array)->toHaveKey('onPostToolUse')
            ->and($array)->toHaveKey('onPostToolUseFailure')
            ->and($array['onPreToolUse'])->toBe($preToolUse)
            ->and($array['onPostToolUse'])->toBe($postToolUse)
            ->and($array['onPostToolUseFailure'])->toBe($postToolUseFailure)
            ->and($array['onSubagentStart'])->toBeCallable()
            ->and($array['onSubagentStop'])->toBeCallable();
    });

    it('filters null values in toArray', function () {
        $hooks = new SessionHooks;

        expect($hooks->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $hooks = new SessionHooks;

        expect($hooks)->toBeInstanceOf(Arrayable::class);
    });
});
