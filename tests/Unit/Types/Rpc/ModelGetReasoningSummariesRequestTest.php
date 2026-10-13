<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ModelGetReasoningSummariesRequest;

describe('ModelGetReasoningSummariesRequest', function () {
    it('defaults optional presentation state to omitted values', function () {
        $request = new ModelGetReasoningSummariesRequest;

        expect($request->persistedShowReasoning)->toBeNull()
            ->and($request->summariesVisible)->toBeNull()
            ->and($request->toArray())->toBe([]);
    });

    it('round trips explicit false and true values', function () {
        $request = ModelGetReasoningSummariesRequest::fromArray([
            'persistedShowReasoning' => false,
            'summariesVisible' => true,
        ]);

        expect($request->toArray())->toBe([
            'persistedShowReasoning' => false,
            'summariesVisible' => true,
        ]);
    });

    it('implements Arrayable', function () {
        expect(new ModelGetReasoningSummariesRequest)->toBeInstanceOf(Arrayable::class);
    });
});
