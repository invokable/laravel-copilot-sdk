<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ContextBoardGetConsolidationPromptResult;

describe('ContextBoardGetConsolidationPromptResult', function () {
    it('round-trips the required prompt', function () {
        $data = ['prompt' => "Consolidate the context.\nKeep important details."];

        $result = ContextBoardGetConsolidationPromptResult::fromArray($data);

        expect($result)->toBeInstanceOf(Arrayable::class)
            ->and($result->prompt)->toBe($data['prompt'])
            ->and($result->toArray())->toBe($data);
    });

    it('rejects a missing prompt', function () {
        ContextBoardGetConsolidationPromptResult::fromArray([]);
    })->throws(InvalidArgumentException::class);
});
