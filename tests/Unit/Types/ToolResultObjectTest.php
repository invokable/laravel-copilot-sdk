<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ToolResultObject;

describe('ToolResultObject', function () {
    it('can be created with all fields', function () {
        $result = new ToolResultObject(
            textResultForLlm: 'Operation completed successfully',
            resultType: 'success',
            binaryResultsForLlm: [['data' => 'base64...', 'mimeType' => 'image/png']],
            error: null,
            sessionLog: 'Tool executed at 12:00',
            toolTelemetry: ['duration' => 100],
        );

        expect($result->textResultForLlm)->toBe('Operation completed successfully')
            ->and($result->resultType)->toBe('success')
            ->and($result->binaryResultsForLlm)->toBe([['data' => 'base64...', 'mimeType' => 'image/png']])
            ->and($result->error)->toBeNull()
            ->and($result->sessionLog)->toBe('Tool executed at 12:00')
            ->and($result->toolTelemetry)->toBe(['duration' => 100]);
    });

    it('can be created with minimal fields', function () {
        $result = new ToolResultObject(
            textResultForLlm: 'Result text',
        );

        expect($result->textResultForLlm)->toBe('Result text')
            ->and($result->resultType)->toBe('success')
            ->and($result->binaryResultsForLlm)->toBeNull()
            ->and($result->error)->toBeNull()
            ->and($result->sessionLog)->toBeNull()
            ->and($result->toolTelemetry)->toBeNull();
    });

    it('can be created with failure result type', function () {
        $result = new ToolResultObject(
            textResultForLlm: 'Operation failed',
            resultType: 'failure',
            error: 'Connection timeout',
        );

        expect($result->resultType)->toBe('failure')
            ->and($result->error)->toBe('Connection timeout');
    });

    it('can be created from array', function () {
        $result = ToolResultObject::fromArray([
            'textResultForLlm' => 'Test result',
            'resultType' => 'rejected',
            'error' => 'Not allowed',
            'sessionLog' => 'Logged info',
            'toolTelemetry' => ['key' => 'value'],
        ]);

        expect($result->textResultForLlm)->toBe('Test result')
            ->and($result->resultType)->toBe('rejected')
            ->and($result->error)->toBe('Not allowed')
            ->and($result->sessionLog)->toBe('Logged info')
            ->and($result->toolTelemetry)->toBe(['key' => 'value']);
    });

    it('can be created from array with defaults', function () {
        $result = ToolResultObject::fromArray([]);

        expect($result->textResultForLlm)->toBe('')
            ->and($result->resultType)->toBe('success');
    });

    it('can convert to array with all fields', function () {
        $result = new ToolResultObject(
            textResultForLlm: 'Full result',
            resultType: 'success',
            binaryResultsForLlm: [['data' => 'abc']],
            error: 'Some error',
            sessionLog: 'Log entry',
            toolTelemetry: ['metric' => 1],
        );

        expect($result->toArray())->toBe([
            'textResultForLlm' => 'Full result',
            'resultType' => 'success',
            'binaryResultsForLlm' => [['data' => 'abc']],
            'error' => 'Some error',
            'sessionLog' => 'Log entry',
            'toolTelemetry' => ['metric' => 1],
        ]);
    });

    it('filters null values in toArray', function () {
        $result = new ToolResultObject(
            textResultForLlm: 'Simple result',
        );

        expect($result->toArray())->toBe([
            'textResultForLlm' => 'Simple result',
            'resultType' => 'success',
        ]);
    });

    it('implements Arrayable interface', function () {
        $result = new ToolResultObject(textResultForLlm: 'Test');

        expect($result)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
