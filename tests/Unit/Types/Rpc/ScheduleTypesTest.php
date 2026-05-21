<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ScheduleEntry;
use Revolution\Copilot\Types\Rpc\ScheduleList;
use Revolution\Copilot\Types\Rpc\ScheduleStopRequest;
use Revolution\Copilot\Types\Rpc\ScheduleStopResult;

describe('ScheduleEntry', function () {
    it('can be created from array with all fields', function () {
        $entry = ScheduleEntry::fromArray([
            'id' => 1,
            'intervalMs' => 60000,
            'nextRunAt' => '2024-01-01T01:00:00Z',
            'prompt' => 'summarize progress',
            'recurring' => true,
            'displayPrompt' => '/every 1m',
        ]);

        expect($entry->id)->toBe(1)
            ->and($entry->intervalMs)->toBe(60000)
            ->and($entry->nextRunAt)->toBe('2024-01-01T01:00:00Z')
            ->and($entry->prompt)->toBe('summarize progress')
            ->and($entry->recurring)->toBeTrue()
            ->and($entry->displayPrompt)->toBe('/every 1m');
    });

    it('can be created from minimal array', function () {
        $entry = ScheduleEntry::fromArray([]);

        expect($entry->id)->toBe(0)
            ->and($entry->intervalMs)->toBe(0)
            ->and($entry->nextRunAt)->toBe('')
            ->and($entry->prompt)->toBe('')
            ->and($entry->recurring)->toBeFalse()
            ->and($entry->displayPrompt)->toBeNull();
    });

    it('converts to array omitting null displayPrompt', function () {
        $entry = new ScheduleEntry(
            id: 2,
            intervalMs: 30000,
            nextRunAt: '2024-01-01T00:30:00Z',
            prompt: 'check status',
            recurring: false,
        );

        $array = $entry->toArray();

        expect($array)->toHaveKeys(['id', 'intervalMs', 'nextRunAt', 'prompt', 'recurring'])
            ->and($array)->not->toHaveKey('displayPrompt')
            ->and($array['id'])->toBe(2)
            ->and($array['recurring'])->toBeFalse();
    });

    it('converts to array including displayPrompt when set', function () {
        $entry = new ScheduleEntry(
            id: 3,
            intervalMs: 3600000,
            nextRunAt: '2024-01-01T02:00:00Z',
            prompt: '/skill invoke-something',
            recurring: true,
            displayPrompt: '/skill-name',
        );

        expect($entry->toArray())->toHaveKey('displayPrompt', '/skill-name');
    });

    it('implements Arrayable', function () {
        expect(new ScheduleEntry(id: 1, intervalMs: 1000, nextRunAt: '', prompt: 'p', recurring: false))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ScheduleList', function () {
    it('can be created from empty array', function () {
        $list = ScheduleList::fromArray([]);

        expect($list->entries)->toBe([]);
    });

    it('can be created with entries', function () {
        $list = ScheduleList::fromArray([
            'entries' => [
                [
                    'id' => 1,
                    'intervalMs' => 60000,
                    'nextRunAt' => '2024-01-01T01:00:00Z',
                    'prompt' => 'summarize',
                    'recurring' => true,
                ],
                [
                    'id' => 2,
                    'intervalMs' => 3600000,
                    'nextRunAt' => '2024-01-01T02:00:00Z',
                    'prompt' => 'report',
                    'recurring' => false,
                ],
            ],
        ]);

        expect($list->entries)->toHaveCount(2)
            ->and($list->entries[0])->toBeInstanceOf(ScheduleEntry::class)
            ->and($list->entries[0]->id)->toBe(1)
            ->and($list->entries[1]->id)->toBe(2);
    });

    it('converts to array', function () {
        $list = new ScheduleList(entries: []);

        expect($list->toArray())->toBe(['entries' => []]);
    });

    it('implements Arrayable', function () {
        expect(new ScheduleList)->toBeInstanceOf(Arrayable::class);
    });
});

describe('ScheduleStopRequest', function () {
    it('can be created and converted', function () {
        $req = new ScheduleStopRequest(id: 5);

        expect($req->id)->toBe(5)
            ->and($req->toArray())->toBe(['id' => 5]);
    });

    it('can be created from array', function () {
        $req = ScheduleStopRequest::fromArray(['id' => 3]);

        expect($req->id)->toBe(3);
    });

    it('implements Arrayable', function () {
        expect(new ScheduleStopRequest(id: 1))->toBeInstanceOf(Arrayable::class);
    });
});

describe('ScheduleStopResult', function () {
    it('can be created with no entry', function () {
        $result = ScheduleStopResult::fromArray([]);

        expect($result->entry)->toBeNull();
    });

    it('can be created with entry', function () {
        $result = ScheduleStopResult::fromArray([
            'entry' => [
                'id' => 1,
                'intervalMs' => 60000,
                'nextRunAt' => '2024-01-01T01:00:00Z',
                'prompt' => 'summarize',
                'recurring' => true,
            ],
        ]);

        expect($result->entry)->toBeInstanceOf(ScheduleEntry::class)
            ->and($result->entry->id)->toBe(1);
    });

    it('converts to array omitting null entry', function () {
        $result = new ScheduleStopResult(entry: null);

        expect($result->toArray())->toBe([]);
    });

    it('converts to array including entry when set', function () {
        $result = new ScheduleStopResult(
            entry: new ScheduleEntry(id: 7, intervalMs: 1000, nextRunAt: '', prompt: 'p', recurring: false),
        );

        expect($result->toArray())->toHaveKey('entry')
            ->and($result->toArray()['entry']['id'])->toBe(7);
    });

    it('implements Arrayable', function () {
        expect(new ScheduleStopResult)->toBeInstanceOf(Arrayable::class);
    });
});
