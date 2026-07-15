<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\SlashCommandAgentPromptResult;

describe('SlashCommandAgentPromptResult', function () {
    it('can be created from array with all fields', function () {
        $result = SlashCommandAgentPromptResult::fromArray([
            'displayPrompt' => 'Display text',
            'prompt' => 'Actual prompt',
            'mode' => 'agent',
            'notice' => 'Please note this change',
            'runtimeSettingsChanged' => true,
        ]);

        expect($result->displayPrompt)->toBe('Display text')
            ->and($result->prompt)->toBe('Actual prompt')
            ->and($result->mode)->toBe('agent')
            ->and($result->notice)->toBe('Please note this change')
            ->and($result->runtimeSettingsChanged)->toBeTrue();
    });

    it('can be created from array with minimal fields', function () {
        $result = SlashCommandAgentPromptResult::fromArray([
            'displayPrompt' => 'Display',
            'prompt' => 'Prompt',
        ]);

        expect($result->displayPrompt)->toBe('Display')
            ->and($result->prompt)->toBe('Prompt')
            ->and($result->mode)->toBeNull()
            ->and($result->notice)->toBeNull()
            ->and($result->runtimeSettingsChanged)->toBeNull();
    });

    it('converts to array correctly', function () {
        $result = new SlashCommandAgentPromptResult(
            displayPrompt: 'Show this',
            prompt: 'Run this',
            notice: 'A notice',
        );

        $array = $result->toArray();

        expect($array)->toHaveKey('displayPrompt', 'Show this')
            ->and($array)->toHaveKey('prompt', 'Run this')
            ->and($array)->toHaveKey('notice', 'A notice')
            ->and($array)->not->toHaveKey('mode')
            ->and($array)->not->toHaveKey('runtimeSettingsChanged');
    });

    it('implements Arrayable', function () {
        expect(new SlashCommandAgentPromptResult(displayPrompt: 'd', prompt: 'p'))->toBeInstanceOf(Arrayable::class);
    });
});
