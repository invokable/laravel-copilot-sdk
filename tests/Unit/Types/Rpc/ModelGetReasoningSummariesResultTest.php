<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ModelGetReasoningSummariesResult;

describe('ModelGetReasoningSummariesResult', function () {
    it('defaults boolean policy fields to false and leaves visibility unset', function () {
        $result = new ModelGetReasoningSummariesResult;

        expect($result->ready)->toBeFalse()
            ->and($result->experimentSettled)->toBeFalse()
            ->and($result->sessionShowsSummaries)->toBeFalse()
            ->and($result->modelDefaultShowsSummaries)->toBeFalse()
            ->and($result->showReasoningHeaders)->toBeFalse()
            ->and($result->visibility)->toBeNull()
            ->and($result->showHiddenHint)->toBeFalse()
            ->and($result->clearRedundantSetting)->toBeFalse();
    });

    it('round trips false values and optional visibility', function () {
        $payload = [
            'ready' => true,
            'experimentSettled' => false,
            'sessionShowsSummaries' => true,
            'modelDefaultShowsSummaries' => false,
            'showReasoningHeaders' => true,
            'visibility' => false,
            'showHiddenHint' => false,
            'clearRedundantSetting' => true,
        ];

        $result = ModelGetReasoningSummariesResult::fromArray($payload);

        expect($result->toArray())->toBe($payload);
    });

    it('omits visibility when the runtime does not provide it', function () {
        $result = ModelGetReasoningSummariesResult::fromArray([]);

        expect($result->toArray())->toBe([
            'ready' => false,
            'experimentSettled' => false,
            'sessionShowsSummaries' => false,
            'modelDefaultShowsSummaries' => false,
            'showReasoningHeaders' => false,
            'showHiddenHint' => false,
            'clearRedundantSetting' => false,
        ]);
    });

    it('implements Arrayable', function () {
        expect(new ModelGetReasoningSummariesResult)->toBeInstanceOf(Arrayable::class);
    });
});
