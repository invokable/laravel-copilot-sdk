<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SecretFilterFormat;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesResult;
use Revolution\Copilot\Types\Rpc\SecretsFilterRequest;
use Revolution\Copilot\Types\Rpc\SecretsFilterResult;
use Revolution\Copilot\Types\Rpc\SecretsRemoveFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsRemoveFilterValuesResult;

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

describe('SecretsFilterRequest', function () {
    it('round-trips both formats and preserves unknown format strings', function (string $format, SecretFilterFormat|string $expected) {
        $data = ['text' => '', 'format' => $format];
        $request = SecretsFilterRequest::fromArray($data);

        expect($request->format)->toBe($expected)
            ->and($request->toArray())->toBe($data);
    })->with([
        ['text', SecretFilterFormat::Text],
        ['json', SecretFilterFormat::Json],
        ['future', 'future'],
    ]);

    it('omits the optional format to use the runtime default', function () {
        $request = SecretsFilterRequest::fromArray(['text' => 'safe']);

        expect($request->format)->toBeNull()
            ->and($request->toArray())->toBe(['text' => 'safe']);
    });

    describe('SecretsFilterResult', function () {
        it('round-trips content and changed flag', function (bool $changed) {
            $data = ['value' => '', 'changed' => $changed];

            expect(SecretsFilterResult::fromArray($data)->toArray())->toBe($data);
        })->with([true, false]);
    });

    describe('SecretsRemoveFilterValues types', function () {
        it('round-trips the values and result', function () {
            $values = ['example', 'another'];

            expect(SecretsRemoveFilterValuesRequest::fromArray(['values' => $values])->toArray())
                ->toBe(['values' => $values])
                ->and((new SecretsRemoveFilterValuesRequest([]))->toArray())->toBe(['values' => []])
                ->and(SecretsRemoveFilterValuesResult::fromArray(['ok' => true])->toArray())->toBe(['ok' => true])
                ->and((new SecretsRemoveFilterValuesResult(false))->toArray())->toBe(['ok' => false]);
        });
    });
});
