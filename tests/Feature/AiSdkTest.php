<?php

declare(strict_types=1);

use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Prompts\AgentPrompt;

use function Laravel\Ai\agent;

describe('Laravel AI SDK', function () {
    test('copilot', function () {
        AnonymousAgent::fake();

        $response = agent(
            instructions: 'You are an expert at software development.',
        )->prompt('Tell me about Laravel');

        AnonymousAgent::assertPrompted(function (AgentPrompt $prompt) {
            return $prompt->contains('Laravel');
        });
    });
});
