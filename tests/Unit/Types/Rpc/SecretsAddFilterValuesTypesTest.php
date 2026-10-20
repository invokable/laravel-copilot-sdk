<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesResult;

describe('SecretsAddFilterValuesRequest', function () {
    it('round-trips filter values', function () {
        $values = ['mcp-token', 'oidc-token'];
        $request = new SecretsAddFilterValuesRequest($values);

        expect($request->toArray())->toBe(['values' => $values])
            ->and(SecretsAddFilterValuesRequest::fromArray($request->toArray())->values)->toBe($values);
    });
});

describe('SecretsAddFilterValuesResult', function () {
    it('round-trips the ok flag', function () {
        $result = new SecretsAddFilterValuesResult(true);

        expect($result->toArray())->toBe(['ok' => true])
            ->and(SecretsAddFilterValuesResult::fromArray($result->toArray())->ok)->toBeTrue();
    });
});
