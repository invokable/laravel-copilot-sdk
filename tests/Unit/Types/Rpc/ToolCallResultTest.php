<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ExternalToolTextResultForLlm;

describe('ExternalToolTextResultForLlm', function () {
    it('can be created from array with all fields', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'The file was created successfully.',
            'resultType' => 'success',
            'error' => null,
            'sessionLog' => 'Executed in 123ms',
            'toolTelemetry' => ['duration' => 123],
            'contents' => [['type' => 'text', 'text' => 'hello']],
        ]);

        expect($result->textResultForLlm)->toBe('The file was created successfully.')
            ->and($result->resultType)->toBe('success')
            ->and($result->error)->toBeNull()
            ->and($result->sessionLog)->toBe('Executed in 123ms')
            ->and($result->toolTelemetry)->toBe(['duration' => 123])
            ->and($result->contents)->toBe([['type' => 'text', 'text' => 'hello']]);
    });

    it('can be created from array with only required field', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'Done.',
        ]);

        expect($result->textResultForLlm)->toBe('Done.')
            ->and($result->resultType)->toBeNull()
            ->and($result->error)->toBeNull()
            ->and($result->sessionLog)->toBeNull()
            ->and($result->toolTelemetry)->toBeNull()
            ->and($result->contents)->toBeNull();
    });

    it('can be created with error message', function () {
        $result = ExternalToolTextResultForLlm::fromArray([
            'textResultForLlm' => 'Tool call failed.',
            'error' => 'File not found.',
        ]);

        expect($result->textResultForLlm)->toBe('Tool call failed.')
            ->and($result->error)->toBe('File not found.');
    });

    it('serializes to array omitting null fields', function () {
        $result = new ExternalToolTextResultForLlm(textResultForLlm: 'Result text.');

        expect($result->toArray())->toBe(['textResultForLlm' => 'Result text.']);
    });

    it('serializes to array with all fields', function () {
        $result = new ExternalToolTextResultForLlm(
            textResultForLlm: 'Output.',
            resultType: 'text',
            sessionLog: 'log output',
            toolTelemetry: ['ms' => 50],
            contents: [['type' => 'text', 'text' => 'block']],
        );

        $array = $result->toArray();

        expect($array)->toHaveKey('textResultForLlm', 'Output.')
            ->and($array)->toHaveKey('resultType', 'text')
            ->and($array)->toHaveKey('sessionLog', 'log output')
            ->and($array)->toHaveKey('toolTelemetry', ['ms' => 50])
            ->and($array)->toHaveKey('contents', [['type' => 'text', 'text' => 'block']])
            ->and($array)->not->toHaveKey('error');
    });

    it('serializes error field when present', function () {
        $result = new ExternalToolTextResultForLlm(
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
            'sessionLog' => 'done',
            'toolTelemetry' => ['key' => 'value'],
        ];

        $array = ExternalToolTextResultForLlm::fromArray($data)->toArray();

        expect($array)->toBe($data);
    });
});
