<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionActivity;

describe('SessionActivity', function () {
    it('preserves activity flags when converting to and from arrays', function () {
        $activity = SessionActivity::fromArray([
            'abortable' => true,
            'hasActiveWork' => false,
        ]);

        expect($activity->abortable)->toBeTrue()
            ->and($activity->hasActiveWork)->toBeFalse()
            ->and($activity->toArray())->toBe([
                'abortable' => true,
                'hasActiveWork' => false,
            ]);
    });

    it('defaults missing activity flags to false', function () {
        $activity = SessionActivity::fromArray([]);

        expect($activity->abortable)->toBeFalse()
            ->and($activity->hasActiveWork)->toBeFalse();
    });
});
