<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelArtifactDetails;

describe('ModelArtifactDetails', function () {
    it('preserves artifact metadata when created from an array', function () {
        $details = ModelArtifactDetails::fromArray([
            'architecture' => 'x86_64',
            'families' => ['llama', 'transformer'],
            'family' => 'llama',
            'format' => 'gguf',
            'parameterSize' => '8B',
            'quantization' => 'Q4_K_M',
            'tokenizer' => 'llama',
        ]);

        expect($details->architecture)->toBe('x86_64')
            ->and($details->families)->toBe(['llama', 'transformer'])
            ->and($details->family)->toBe('llama')
            ->and($details->format)->toBe('gguf')
            ->and($details->parameterSize)->toBe('8B')
            ->and($details->quantization)->toBe('Q4_K_M')
            ->and($details->tokenizer)->toBe('llama')
            ->and($details->toArray())->toBe([
                'architecture' => 'x86_64',
                'families' => ['llama', 'transformer'],
                'family' => 'llama',
                'format' => 'gguf',
                'parameterSize' => '8B',
                'quantization' => 'Q4_K_M',
                'tokenizer' => 'llama',
            ]);
    });

    it('defaults missing metadata to null and omits it from the array', function () {
        $details = ModelArtifactDetails::fromArray([]);

        expect($details->architecture)->toBeNull()
            ->and($details->families)->toBeNull()
            ->and($details->family)->toBeNull()
            ->and($details->format)->toBeNull()
            ->and($details->parameterSize)->toBeNull()
            ->and($details->quantization)->toBeNull()
            ->and($details->tokenizer)->toBeNull()
            ->and($details->toArray())->toBe([]);
    });
});
