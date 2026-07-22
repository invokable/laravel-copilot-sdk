<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryAgentResult;

describe('FactoryAgentResult', function () {
    it('can be created from array', function () {
        $result = FactoryAgentResult::fromArray(['result' => ['foo' => 'bar']]);

        expect($result->result)->toBe(['foo' => 'bar']);
    });

    it('defaults result to null', function () {
        $result = FactoryAgentResult::fromArray([]);

        expect($result->result)->toBeNull();
    });

    it('converts to array correctly', function () {
        $result = new FactoryAgentResult(result: 'value');

        expect($result->toArray())->toBe(['result' => 'value']);
    });
});
