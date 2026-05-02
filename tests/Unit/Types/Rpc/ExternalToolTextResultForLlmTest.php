<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ExternalToolTextResultForLlm;

describe('ExternalToolTextResultForLlm', function () {
    it('can be created from array with all fields', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'Test passed successfully',
            'resultType' => 'success',
            'error' => null,
            'sessionLog' => 'Running tests...\nAll passed.',
            'toolTelemetry' => ['duration_ms' => 120],
            'contents' => [['type' => 'text', 'text' => 'output']],
        ]);

        expect($result->textResultForLlm)->toBe('Test passed successfully')
            ->and($result->resultType)->toBe('success')
            ->and($result->error)->toBeNull()
            ->and($result->sessionLog)->toBe('Running tests...\nAll passed.')
            ->and($result->toolTelemetry)->toBe(['duration_ms' => 120])
            ->and($result->contents)->toBe([['type' => 'text', 'text' => 'output']]);
    });

    it('can be created from minimal array with only required field', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'minimal result',
        ]);

        expect($result->textResultForLlm)->toBe('minimal result')
            ->and($result->resultType)->toBeNull()
            ->and($result->error)->toBeNull()
            ->and($result->sessionLog)->toBeNull()
            ->and($result->toolTelemetry)->toBeNull()
            ->and($result->contents)->toBeNull();
    });

    it('can be created with error field', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'command failed',
            'resultType' => 'failure',
            'error' => 'Exit code 1',
        ]);

        expect($result->resultType)->toBe('failure')
            ->and($result->error)->toBe('Exit code 1');
    });

    it('converts to array omitting null values', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'output',
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('textResultForLlm', 'output')
            ->and($array)->not->toHaveKey('resultType')
            ->and($array)->not->toHaveKey('error')
            ->and($array)->not->toHaveKey('sessionLog')
            ->and($array)->not->toHaveKey('toolTelemetry')
            ->and($array)->not->toHaveKey('contents');
    });

    it('converts to array including all non-null fields', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'done',
            'resultType' => 'success',
            'sessionLog' => 'log output',
            'toolTelemetry' => ['key' => 'value'],
            'contents' => [],
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('textResultForLlm', 'done')
            ->and($array)->toHaveKey('resultType', 'success')
            ->and($array)->toHaveKey('sessionLog', 'log output')
            ->and($array)->toHaveKey('toolTelemetry', ['key' => 'value']);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'textResultForLlm' => 'roundtrip result',
            'resultType' => 'success',
            'error' => 'some error',
            'sessionLog' => 'log',
            'toolTelemetry' => ['a' => 1],
            'contents' => [['type' => 'code', 'text' => 'echo hello']],
        ];

        $result = ExternalToolTextResultForLlm::fromArray($data);
        $array = $result->toArray();

        expect($array)->toBe($data);
    });
});
