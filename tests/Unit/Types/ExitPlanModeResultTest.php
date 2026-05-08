<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ExitPlanModeResult;

describe('ExitPlanModeResult', function () {
    it('can be created from array with all fields', function () {
        $result = ExitPlanModeResult::fromArray([
            'approved' => true,
            'selectedAction' => 'approve',
            'feedback' => 'Looks good',
        ]);

        expect($result->approved)->toBeTrue()
            ->and($result->selectedAction)->toBe('approve')
            ->and($result->feedback)->toBe('Looks good');
    });

    it('handles missing optional fields', function () {
        $result = ExitPlanModeResult::fromArray([
            'approved' => false,
        ]);

        expect($result->approved)->toBeFalse()
            ->and($result->selectedAction)->toBeNull()
            ->and($result->feedback)->toBeNull();
    });

    it('defaults approved to false when array is empty', function () {
        $result = ExitPlanModeResult::fromArray([]);

        expect($result->approved)->toBeFalse();
    });

    it('converts to array filtering null values', function () {
        $result = ExitPlanModeResult::fromArray([
            'approved' => true,
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('approved', true)
            ->and($array)->not->toHaveKey('selectedAction')
            ->and($array)->not->toHaveKey('feedback');
    });

    it('implements Arrayable', function () {
        expect(new ExitPlanModeResult(true))->toBeInstanceOf(Arrayable::class);
    });
});
