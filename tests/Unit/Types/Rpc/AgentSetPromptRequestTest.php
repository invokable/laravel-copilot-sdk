<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AgentSetPromptRequest;

describe('AgentSetPromptRequest', function () {
    it('creates a request from array data', function () {
        $request = AgentSetPromptRequest::fromArray([
            'id' => 'code-reviewer',
            'prompt' => 'Review the code for correctness.',
        ]);

        expect($request->id)->toBe('code-reviewer')
            ->and($request->prompt)->toBe('Review the code for correctness.');
    });

    it('converts the request to array data', function () {
        $request = new AgentSetPromptRequest(
            id: 'code-reviewer',
            prompt: 'Review the code for correctness.',
        );

        expect($request->toArray())->toBe([
            'id' => 'code-reviewer',
            'prompt' => 'Review the code for correctness.',
        ]);
    });

    it('preserves empty strings', function () {
        $request = AgentSetPromptRequest::fromArray([
            'id' => '',
            'prompt' => '',
        ]);

        expect($request->id)->toBe('')
            ->and($request->prompt)->toBe('')
            ->and($request->toArray())->toBe([
                'id' => '',
                'prompt' => '',
            ]);
    });
});
