<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ExitPlanModeRequest;

describe('ExitPlanModeRequest', function () {
    it('can be created from array with all fields', function () {
        $request = ExitPlanModeRequest::fromArray([
            'summary' => 'Plan summary',
            'actions' => ['approve', 'reject', 'modify'],
            'recommendedAction' => 'approve',
            'planContent' => 'Full plan content here',
        ]);

        expect($request->summary)->toBe('Plan summary')
            ->and($request->actions)->toBe(['approve', 'reject', 'modify'])
            ->and($request->recommendedAction)->toBe('approve')
            ->and($request->planContent)->toBe('Full plan content here');
    });

    it('handles missing optional fields', function () {
        $request = ExitPlanModeRequest::fromArray([
            'summary' => 'Plan summary',
            'actions' => ['approve'],
            'recommendedAction' => 'approve',
        ]);

        expect($request->planContent)->toBeNull();
    });

    it('defaults to empty values when array is empty', function () {
        $request = ExitPlanModeRequest::fromArray([]);

        expect($request->summary)->toBe('')
            ->and($request->actions)->toBe([])
            ->and($request->recommendedAction)->toBe('')
            ->and($request->planContent)->toBeNull();
    });

    it('converts to array filtering null values', function () {
        $request = ExitPlanModeRequest::fromArray([
            'summary' => 'Plan summary',
            'actions' => ['approve'],
            'recommendedAction' => 'approve',
        ]);

        $array = $request->toArray();

        expect($array)->toHaveKey('summary', 'Plan summary')
            ->and($array)->toHaveKey('actions', ['approve'])
            ->and($array)->toHaveKey('recommendedAction', 'approve')
            ->and($array)->not->toHaveKey('planContent');
    });

    it('implements Arrayable', function () {
        expect(new ExitPlanModeRequest('s', [], 'a'))->toBeInstanceOf(Arrayable::class);
    });
});
