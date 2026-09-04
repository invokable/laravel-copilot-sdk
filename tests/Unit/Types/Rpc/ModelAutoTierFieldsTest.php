<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\TaskKind;
use Revolution\Copilot\Types\Rpc\ConnectRequest;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;

describe('CurrentModel auto tier fields', function () {
    it('can be created with auto tier fields', function () {
        $model = new CurrentModel(
            modelId: 'auto',
            autoTier: AutoTier::BALANCE,
            activatingAutoTier: AutoTier::INTELLIGENCE,
            pendingAutoTier: AutoTier::EFFICIENCY,
        );

        expect($model->modelId)->toBe('auto')
            ->and($model->autoTier)->toBe(AutoTier::BALANCE)
            ->and($model->activatingAutoTier)->toBe(AutoTier::INTELLIGENCE)
            ->and($model->pendingAutoTier)->toBe(AutoTier::EFFICIENCY);
    });

    it('defaults auto tier fields to null', function () {
        $model = CurrentModel::fromArray(['modelId' => 'gpt-4o']);

        expect($model->autoTier)->toBeNull()
            ->and($model->activatingAutoTier)->toBeNull()
            ->and($model->pendingAutoTier)->toBeNull();
    });

    it('roundtrips auto tier fields through fromArray/toArray', function () {
        $data = [
            'modelId' => 'auto',
            'autoTier' => 'balance',
            'activatingAutoTier' => 'intelligence',
            'pendingAutoTier' => 'efficiency',
        ];

        expect(CurrentModel::fromArray($data)->toArray())->toBe($data);
    });
});

describe('ModelSwitchToRequest auto tier field', function () {
    it('serializes autoTier when provided', function () {
        $request = new ModelSwitchToRequest(modelId: 'auto', autoTier: AutoTier::INTELLIGENCE);

        expect($request->toArray())->toBe([
            'modelId' => 'auto',
            'autoTier' => 'intelligence',
        ]);
    });

    it('omits autoTier when not provided', function () {
        $request = new ModelSwitchToRequest(modelId: 'gpt-4o');

        expect($request->toArray())->toBe(['modelId' => 'gpt-4o']);
    });
});

describe('ConnectRequest supportedTaskKinds', function () {
    it('serializes enum values', function () {
        $request = new ConnectRequest(supportedTaskKinds: [TaskKind::AGENT, TaskKind::CLIENT, TaskKind::SHELL]);

        expect($request->toArray())->toBe([
            'supportedTaskKinds' => ['agent', 'client', 'shell'],
        ]);
    });

    it('omits supportedTaskKinds when null', function () {
        $request = new ConnectRequest;

        expect($request->toArray())->toBe([]);
    });

    it('can be created from array', function () {
        $request = ConnectRequest::fromArray(['supportedTaskKinds' => ['agent', 'shell']]);

        expect($request->supportedTaskKinds)->toBe(['agent', 'shell']);
    });
});
