<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainRequest;
use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainRequest;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\ScheduleAddAtRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddCronRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddRequest;
use Revolution\Copilot\Types\Rpc\ScheduleAddResult;
use Revolution\Copilot\Types\Rpc\ScheduleAddSelfPacedRequest;
use Revolution\Copilot\Types\Rpc\ScheduleEntry;

describe('QueueBeginDeferredIdleDrainRequest', function () {
    it('roundtrips', function () {
        expect(QueueBeginDeferredIdleDrainRequest::fromArray(['activeBackgroundWork' => true])->toArray())
            ->toBe(['activeBackgroundWork' => true]);
    });
});

describe('QueueBeginDeferredIdleDrainResult', function () {
    it('roundtrips', function () {
        expect(QueueBeginDeferredIdleDrainResult::fromArray(['shouldDrain' => false])->toArray())
            ->toBe(['shouldDrain' => false]);
    });

    describe('QueueFinishDeferredIdleDrainRequest', function () {
        it('roundtrips', function () {
            expect(QueueFinishDeferredIdleDrainRequest::fromArray([
                'activeBackgroundWork' => false,
                'hasPending' => true,
            ])->toArray())->toBe([
                'activeBackgroundWork' => false,
                'hasPending' => true,
            ]);
        });
    });

    describe('QueueFinishDeferredIdleDrainResult', function () {
        it('roundtrips', function () {
            expect(QueueFinishDeferredIdleDrainResult::fromArray([
                'action' => 'processQueue',
                'aborted' => false,
            ])->toArray())->toBe([
                'action' => 'processQueue',
                'aborted' => false,
            ]);
        });
    });
});

describe('ScheduleAddRequest', function () {
    it('keeps only provided fields', function () {
        $request = ScheduleAddRequest::fromArray([
            'interval' => '30m',
            'prompt' => 'check',
            'recurring' => true,
        ]);

        expect($request->toArray())->toBe([
            'interval' => '30m',
            'prompt' => 'check',
            'recurring' => true,
        ]);
    });
});

describe('ScheduleAddAtRequest', function () {
    it('roundtrips an absolute schedule', function () {
        $request = ScheduleAddAtRequest::fromArray([
            'at' => 1735689600000,
            'prompt' => 'fire once',
        ]);

        expect($request->at)->toBe(1735689600000)
            ->and($request->toArray())->toBe(['at' => 1735689600000, 'prompt' => 'fire once']);
    });
});

describe('ScheduleAddCronRequest', function () {
    it('carries cron and timezone', function () {
        $request = ScheduleAddCronRequest::fromArray([
            'cron' => '0 9 * * *',
            'prompt' => 'daily',
            'tz' => 'Asia/Tokyo',
        ]);

        expect($request->toArray())->toBe([
            'cron' => '0 9 * * *',
            'prompt' => 'daily',
            'tz' => 'Asia/Tokyo',
        ]);
    });
});

describe('ScheduleAddSelfPacedRequest', function () {
    it('roundtrips', function () {
        expect(ScheduleAddSelfPacedRequest::fromArray(['prompt' => 'wakeup'])->toArray())
            ->toBe(['prompt' => 'wakeup']);
    });
});

describe('ScheduleAddResult', function () {
    it('nests the created entry', function () {
        $result = ScheduleAddResult::fromArray([
            'entry' => [
                'id' => 1,
                'intervalMs' => 1800000,
                'nextRunAt' => '2026-01-01T00:00:00Z',
                'prompt' => 'check',
                'recurring' => true,
            ],
        ]);

        expect($result->entry)->toBeInstanceOf(ScheduleEntry::class)
            ->and($result->entry->id)->toBe(1)
            ->and($result->toArray()['entry']['prompt'])->toBe('check');
    });

    it('carries an error', function () {
        expect(ScheduleAddResult::fromArray(['error' => 'bad cron'])->toArray())->toBe(['error' => 'bad cron']);
    });
});
