<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ScheduleHasSelfPacedResult;
use Revolution\Copilot\Types\Rpc\ScheduleRearmSelfPacedRequest;

describe('Schedule self-paced types', function () {
    it('round trips the self-paced status result', function () {
        $result = ScheduleHasSelfPacedResult::fromArray(['hasSelfPaced' => true]);

        expect($result->hasSelfPaced)->toBeTrue()
            ->and($result->toArray())->toBe(['hasSelfPaced' => true]);
    });

    it('round trips the rearm request', function () {
        $request = ScheduleRearmSelfPacedRequest::fromArray([
            'id' => 7,
            'at' => 1_725_000_000_000,
        ]);

        expect($request->id)->toBe(7)
            ->and($request->at)->toBe(1_725_000_000_000)
            ->and($request->toArray())->toBe([
                'id' => 7,
                'at' => 1_725_000_000_000,
            ]);
    });
});
