<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryLogLineKind;

describe('FactoryLogLineKind', function () {
    it('has log and phase cases', function () {
        expect(FactoryLogLineKind::LOG->value)->toBe('log')
            ->and(FactoryLogLineKind::PHASE->value)->toBe('phase');
    });

    it('can create from string', function () {
        expect(FactoryLogLineKind::from('log'))->toBe(FactoryLogLineKind::LOG)
            ->and(FactoryLogLineKind::from('phase'))->toBe(FactoryLogLineKind::PHASE);
    });
});
