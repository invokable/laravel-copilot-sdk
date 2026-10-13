<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Types\Rpc\ModelSetReasoningEffortRequest;
use Revolution\Copilot\Types\Rpc\ModelSetReasoningEffortResult;

describe('ModelSetReasoningEffortRequest', function () {
    it('serializes enum and string values', function () {
        $request = new ModelSetReasoningEffortRequest(reasoningEffort: ReasoningEffort::HIGH);
        $stringRequest = new ModelSetReasoningEffortRequest(reasoningEffort: 'xhigh');

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->toArray())->toBe(['reasoningEffort' => 'high'])
            ->and($stringRequest->toArray())->toBe(['reasoningEffort' => 'xhigh']);
    });

    it('restores the enum value from an array', function () {
        $request = ModelSetReasoningEffortRequest::fromArray(['reasoningEffort' => 'high']);

        expect($request->reasoningEffort)->toBe(ReasoningEffort::HIGH)
            ->and($request->toArray())->toBe(['reasoningEffort' => 'high']);
    });
});

describe('ModelSetReasoningEffortResult', function () {
    it('serializes enum and string values', function () {
        $result = new ModelSetReasoningEffortResult(reasoningEffort: ReasoningEffort::MEDIUM);
        $stringResult = new ModelSetReasoningEffortResult(reasoningEffort: 'custom');

        expect($result)->toBeInstanceOf(Arrayable::class)
            ->and($result->toArray())->toBe(['reasoningEffort' => 'medium'])
            ->and($stringResult->toArray())->toBe(['reasoningEffort' => 'custom']);
    });

    it('restores the enum value from an array', function () {
        $result = ModelSetReasoningEffortResult::fromArray(['reasoningEffort' => 'high']);

        expect($result->reasoningEffort)->toBe(ReasoningEffort::HIGH)
            ->and($result->toArray())->toBe(['reasoningEffort' => 'high']);
    });
});
