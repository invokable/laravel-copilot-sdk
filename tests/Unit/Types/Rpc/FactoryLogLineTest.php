<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryLogLineKind;
use Revolution\Copilot\Types\Rpc\FactoryLogLine;

describe('FactoryLogLine', function () {
    it('can be created from array', function () {
        $line = FactoryLogLine::fromArray([
            'kind' => 'log',
            'seq' => 1,
            'text' => 'Starting',
        ]);

        expect($line->kind)->toBe(FactoryLogLineKind::LOG)
            ->and($line->seq)->toBe(1)
            ->and($line->text)->toBe('Starting');
    });

    it('converts to array correctly', function () {
        $line = new FactoryLogLine(kind: FactoryLogLineKind::PHASE, seq: 2, text: 'Phase 2');

        expect($line->toArray())->toBe([
            'kind' => 'phase',
            'seq' => 2,
            'text' => 'Phase 2',
        ]);
    });
});
