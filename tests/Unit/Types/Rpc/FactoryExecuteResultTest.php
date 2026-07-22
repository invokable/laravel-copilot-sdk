<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryExecuteResult;

describe('FactoryExecuteResult', function () {
    it('can be created from array', function () {
        $result = FactoryExecuteResult::fromArray(['result' => 'value']);

        expect($result->result)->toBe('value');
    });

    it('converts to array correctly', function () {
        $result = new FactoryExecuteResult(result: 'value');

        expect($result->toArray())->toBe(['result' => 'value']);
    });
});
