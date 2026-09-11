<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunStatus;
use Revolution\Copilot\Types\Rpc\FactoryPauseInfo;
use Revolution\Copilot\Types\Rpc\FactoryRunTerminal;

describe('FactoryRunTerminal', function () {
    it('can be created from array with pause info', function () {
        $terminal = FactoryRunTerminal::fromArray([
            'reason' => 'paused',
            'pauseInfo' => ['type' => 'checkpoint', 'key' => 'checkpoint-1'],
        ]);

        expect($terminal->reason)->toBe('paused')
            ->and($terminal->pauseInfo)->toBeInstanceOf(FactoryPauseInfo::class)
            ->and($terminal->pauseInfo->key)->toBe('checkpoint-1');
    });

    it('handles default null pauseInfo', function () {
        $terminal = new FactoryRunTerminal(reason: 'completed');

        expect($terminal->pauseInfo)->toBeNull()
            ->and($terminal->toArray())->toBe(['reason' => 'completed']);
    });

    it('roundtrips through fromArray/toArray with pauseInfo', function () {
        $data = [
            'reason' => 'paused',
            'pauseInfo' => ['type' => 'user'],
        ];

        expect(FactoryRunTerminal::fromArray($data)->toArray())->toBe($data);
    });
});

describe('FactoryRunStatus paused', function () {
    it('supports the paused status value', function () {
        expect(FactoryRunStatus::from('paused'))->toBe(FactoryRunStatus::PAUSED);
    });
});
