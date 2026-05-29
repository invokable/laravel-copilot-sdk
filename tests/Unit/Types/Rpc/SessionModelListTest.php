<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelListRequest;
use Revolution\Copilot\Types\Rpc\SessionModelList;

describe('SessionModelList', function () {
    it('can be created from array with all fields', function () {
        $result = SessionModelList::fromArray([
            'list' => [['id' => 'gpt-4'], ['id' => 'claude-3']],
            'quotaSnapshots' => ['daily' => ['remaining' => 100]],
        ]);

        expect($result->list)->toHaveCount(2)
            ->and($result->quotaSnapshots)->toBe(['daily' => ['remaining' => 100]]);
    });

    it('can be created from empty array', function () {
        $result = SessionModelList::fromArray([]);

        expect($result->list)->toBe([])
            ->and($result->quotaSnapshots)->toBeNull();
    });

    it('converts to array correctly', function () {
        $result = SessionModelList::fromArray([
            'list' => [['id' => 'gpt-4']],
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('list')
            ->and($array['list'])->toHaveCount(1);
    });
});

describe('ModelListRequest', function () {
    it('can be created with skipCache', function () {
        $request = new ModelListRequest(skipCache: true);

        expect($request->toArray())->toBe(['skipCache' => true]);
    });

    it('filters null skipCache', function () {
        $request = new ModelListRequest;

        expect($request->toArray())->toBe([]);
    });

    it('can be created from array', function () {
        $request = ModelListRequest::fromArray(['skipCache' => true]);

        expect($request->skipCache)->toBeTrue();
    });
});
