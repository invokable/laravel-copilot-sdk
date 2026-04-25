<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ToolCallResult;

describe('ToolCallResult', function () {
    it('can be created from array with all fields', function () {
        $result = ToolCallResult::fromArray([
            'textResultForLlm' => 'The file was created successfully.',
            'resultType' => 'success',
            'error' => null,
            'toolTelemetry' => ['duration' => 123],
        ]);

        expect($result->textResultForLlm)->toBe('The file was created successfully.')
            ->and($result->resultType)->toBe('success')
            ->and($result->error)->toBeNull()
            ->and($result->toolTelemetry)->toBe(['duration' => 123]);
    });

    it('can be created from array with only required field', function () {
        $result = ToolCallResult::fromArray([
            'textResultForLlm' => 'Done.',
        ]);

        expect($result->textResultForLlm)->toBe('Done.')
            ->and($result->resultType)->toBeNull()
            ->and($result->error)->toBeNull()
            ->and($result->toolTelemetry)->toBeNull();
    });

    it('can be created with error message', function () {
        $result = ToolCallResult::fromArray([
            'textResultForLlm' => 'Tool call failed.',
            'error' => 'File not found.',
        ]);

        expect($result->textResultForLlm)->toBe('Tool call failed.')
            ->and($result->error)->toBe('File not found.');
    });

    it('serializes to array omitting null fields', function () {
        $result = new ToolCallResult(textResultForLlm: 'Result text.');

        expect($result->toArray())->toBe(['textResultForLlm' => 'Result text.']);
    });

    it('serializes to array with all fields', function () {
        $result = new ToolCallResult(
            textResultForLlm: 'Output.',
            resultType: 'text',
            error: null,
            toolTelemetry: ['ms' => 50],
        );

        $array = $result->toArray();

        expect($array)->toHaveKey('textResultForLlm', 'Output.')
            ->and($array)->toHaveKey('resultType', 'text')
            ->and($array)->toHaveKey('toolTelemetry', ['ms' => 50])
            ->and($array)->not->toHaveKey('error');
    });

    it('serializes error field when present', function () {
        $result = new ToolCallResult(
            textResultForLlm: 'Failed.',
            error: 'Permission denied.',
        );

        $array = $result->toArray();

        expect($array)->toHaveKey('error', 'Permission denied.')
            ->and($array)->not->toHaveKey('resultType')
            ->and($array)->not->toHaveKey('toolTelemetry');
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'textResultForLlm' => 'Success.',
            'resultType' => 'success',
            'toolTelemetry' => ['key' => 'value'],
        ];

        $array = ToolCallResult::fromArray($data)->toArray();

        expect($array)->toBe($data);
    });
});
